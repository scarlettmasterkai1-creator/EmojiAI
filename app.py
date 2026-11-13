
from flask import Flask, request, jsonify
from flask_cors import CORS

import base64
import binascii
import json
from pathlib import Path

import numpy as np
import cv2


# ==========================================
# 1. APPLICATION AND CONFIGURATION
# ==========================================

BASE_DIR = Path(__file__).resolve().parent
CONFIG_PATH = BASE_DIR / "config.json"

if not CONFIG_PATH.is_file():
    raise FileNotFoundError(
        f"Configuration file not found: {CONFIG_PATH}"
    )

with open(CONFIG_PATH, "r", encoding="utf-8") as file:
    config = json.load(file)

app = Flask(__name__)

max_image_size = int(config.get("max_image_size_mb", 5))

app.config["MAX_CONTENT_LENGTH"] = (
    max_image_size * 1024 * 1024
)


# ==========================================
# 2. CORS CONFIGURATION
# ==========================================

allowed_origins = config.get(
    "allowed_origins",
    [
        "http://localhost",
        "http://127.0.0.1",
        "http://localhost:8080",
        "http://127.0.0.1:8080"
    ]
)

CORS(
    app,
    resources={
        r"/detect": {
            "origins": allowed_origins
        }
    }
)


# ==========================================
# 3. LOAD AI MODEL
# ==========================================

emotion_model = None
model_error = None

try:
    from emotion_model import EmotionModel

    emotion_model = EmotionModel()

    if emotion_model.model is None:
        model_error = (
            "The trained model file was not loaded. "
            "Train the model and check its file path."
        )
        emotion_model = None

    else:
        app.logger.info(
            "EmojiAI model loaded successfully."
        )

except Exception as error:
    model_error = str(error)

    app.logger.exception(
        "EmojiAI model could not be loaded."
    )


# ==========================================
# 4. HEALTH CHECK
# ==========================================

@app.route("/health", methods=["GET"])
def health():

    return jsonify({
        "success": True,
        "application": "EmojiAI",
        "server": "online",
        "ai_model_ready": emotion_model is not None,
        "model_error": model_error
    }), 200


# ==========================================
# 5. HOME ROUTE
# ==========================================

@app.route("/", methods=["GET"])
def home():

    return jsonify({
        "success": True,
        "message": "EmojiAI Python backend is running"
    }), 200


# ==========================================
# 6. EMOTION DETECTION
# ==========================================

@app.route("/detect", methods=["POST"])
def detect():

    if emotion_model is None:
        return jsonify({
            "success": False,
            "error": (
                "The AI model is not ready. "
                "Check /health for the model error."
            ),
            "code": "MODEL_NOT_READY"
        }), 503

    payload = request.get_json(silent=True) or {}
    image_data = payload.get("image")

    if not isinstance(image_data, str) or not image_data.strip():
        return jsonify({
            "success": False,
            "error": "An image field is required."
        }), 400

    # Accept a data URL or raw Base64.
    if image_data.startswith("data:"):

        if "," not in image_data:
            return jsonify({
                "success": False,
                "error": "Invalid image data URL."
            }), 400

        image_data = image_data.split(",", 1)[1]

    # Decode image.
    try:
        image_bytes = base64.b64decode(
            image_data,
            validate=True
        )

    except (binascii.Error, ValueError):
        return jsonify({
            "success": False,
            "error": "Invalid Base64 image data."
        }), 400

    image_array = np.frombuffer(
        image_bytes,
        dtype=np.uint8
    )

    frame = cv2.imdecode(
        image_array,
        cv2.IMREAD_COLOR
    )

    if frame is None or frame.size == 0:
        return jsonify({
            "success": False,
            "error": "The uploaded image could not be read."
        }), 400

        # ======================================
    # RUN EMOTION PREDICTION
    # ======================================

    try:

        app.logger.info(
            "Received image: width=%s, height=%s",
            frame.shape[1],
            frame.shape[0]
        )

        result = emotion_model.predict(frame)

        if not isinstance(result, dict):
            raise ValueError(
                "The emotion model returned an invalid response."
            )

        emotion = result.get("emotion")
        emoji = result.get("emoji")
        confidence = result.get("confidence")

        if not emotion or not emoji or confidence is None:
            raise ValueError(
                "The model returned incomplete prediction results."
            )

        confidence = float(confidence)

        if not np.isfinite(confidence):
            raise ValueError(
                "The confidence value is not a valid number."
            )

        if not 0 <= confidence <= 100:
            raise ValueError(
                "Confidence must be between 0 and 100."
            )

        app.logger.info(
            "Prediction successful: emotion=%s, confidence=%.2f%%",
            emotion,
            confidence
        )

        return jsonify({
            "success": True,
            "emotion": str(emotion),
            "emoji": str(emoji),
            "confidence": round(confidence, 2)
        }), 200

    except ValueError as error:

        # Image or prediction validation problem.
        app.logger.warning(
            "Image prediction could not be completed: %s",
            error
        )

        return jsonify({
            "success": False,
            "error": str(error),
            "code": "IMAGE_OR_PREDICTION_ERROR"
        }), 422

    except Exception:

        # Unexpected backend/model failure.
        app.logger.exception(
            "Unexpected error during EmojiAI prediction."
        )

        return jsonify({
            "success": False,
            "error": "An unexpected error occurred while processing the image.",
            "code": "PREDICTION_FAILED"
        }), 500

# ==========================================
# 7. IMAGE SIZE ERROR
# ==========================================

@app.errorhandler(413)
def image_too_large(error):

    return jsonify({
        "success": False,
        "error": (
            f"Image is too large. "
            f"Maximum size is {max_image_size} MB."
        )
    }), 413


# ==========================================
# 8. START SERVER
# KEEP THIS AT THE VERY BOTTOM
# ==========================================

if __name__ == "__main__":

    app.run(
        host=config.get("api_host", "127.0.0.1"),
        port=int(config.get("api_port", 5000)),
        debug=bool(config.get("debug", True)),
        use_reloader=False
    )
