
from pathlib import Path

import cv2
import numpy as np
from tensorflow.keras.models import load_model


# ==========================================
# EMOJIAI MODEL CONFIGURATION
# ==========================================

BASE_DIR = Path(__file__).resolve().parent
MODEL_PATH = BASE_DIR / "models" / "emotion_model.keras"

IMAGE_SIZE = 48

LABELS = [
    "Angry",
    "Happy",
    "Neutral",
    "Sad",
    "Surprise"
]

EMOJIS = {
    "Angry": "😠",
    "Happy": "😄",
    "Neutral": "😐",
    "Sad": "😢",
    "Surprise": "😲"
}


# ==========================================
# CUSTOM MODEL ERROR
# ==========================================

class ModelNotConfigured(RuntimeError):
    pass


# ==========================================
# EMOJIAI EMOTION RECOGNITION
# ==========================================

class EmotionModel:

    def __init__(self):

        self.model = None
        self.model_error = None

        print("-----------------------------------")
        print("EmojiAI Emotion Recognition")
        print("-----------------------------------")
        print("Backend folder:", BASE_DIR)
        print("Model location:", MODEL_PATH)

        # ----------------------------------
        # LOAD TRAINED MODEL
        # ----------------------------------

        if not MODEL_PATH.exists():

            self.model_error = (
                f"Model file not found: {MODEL_PATH}"
            )

            print("WARNING:", self.model_error)

        elif not MODEL_PATH.is_file():

            self.model_error = (
                "The model path does not point to a file."
            )

            print("WARNING:", self.model_error)

        elif MODEL_PATH.stat().st_size == 0:

            self.model_error = (
                "The emotion_model.keras file is empty. "
                "Train the AI model before using predictions."
            )

            print("WARNING:", self.model_error)

        else:

            try:

                # compile=False avoids requiring the original
                # optimizer and loss configuration for inference.
                self.model = load_model(
                    str(MODEL_PATH),
                    compile=False
                )

                self._validate_model()

                print("AI model loaded successfully!")
                print("Input shape:", self.model.input_shape)
                print("Output shape:", self.model.output_shape)

            except Exception as exc:

                self.model = None

                self.model_error = (
                    f"Could not load the trained model: {exc}"
                )

                print("MODEL LOADING ERROR:", self.model_error)

        # ----------------------------------
        # LOAD OPENCV FACE DETECTOR
        # ----------------------------------

        cascade_path = (
            cv2.data.haarcascades
            + "haarcascade_frontalface_default.xml"
        )

        self.face_detector = cv2.CascadeClassifier(
            cascade_path
        )

        if self.face_detector.empty():

            raise RuntimeError(
                "OpenCV face detector could not be loaded."
            )

        print("OpenCV face detector loaded.")

        if self.model is None:
            print("AI predictions are currently unavailable.")
        else:
            print("EmojiAI is ready for predictions.")

        print("-----------------------------------")

    # ======================================
    # VALIDATE TRAINED MODEL
    # ======================================

    def _validate_model(self):

        input_shape = self.model.input_shape
        output_shape = self.model.output_shape

        # Only support one image input.
        if isinstance(input_shape, list):

            raise ValueError(
                "Only single-input image models are supported."
            )

        if len(input_shape) != 4:

            raise ValueError(
                "Expected model input shape: "
                "(batch, height, width, channels)."
            )

        # Check image channels.
        channels = input_shape[-1]

        if channels not in (1, 3):

            raise ValueError(
                f"Unsupported image channels: {channels}"
            )

        # Only support five emotion outputs.
        if isinstance(output_shape, list):

            raise ValueError(
                "Only single-output classification models "
                "are supported."
            )

        if len(output_shape) != 2:

            raise ValueError(
                "Expected model output shape: (batch, 5)."
            )

        if output_shape[-1] != len(LABELS):

            raise ValueError(
                "The trained model must have exactly five "
                "output classes."
            )

    # ======================================
    # FACIAL EXPRESSION PREDICTION
    # ======================================

    def predict(self, frame):

        # ----------------------------------
        # CHECK MODEL AVAILABILITY
        # ----------------------------------

        if self.model is None:

            raise ModelNotConfigured(
                self.model_error
                or "The EmojiAI model is not configured."
            )

        # ----------------------------------
        # VALIDATE CAPTURED IMAGE
        # ----------------------------------

        if (
            frame is None
            or not isinstance(frame, np.ndarray)
            or frame.size == 0
        ):

            raise ValueError(
                "The captured image is empty or invalid."
            )

        if frame.ndim != 3 or frame.shape[2] != 3:

            raise ValueError(
                "Expected a colour image with three channels."
            )

        # ==================================
        # DETECT FACE
        # ==================================

        gray = cv2.cvtColor(
            frame,
            cv2.COLOR_BGR2GRAY
        )

        faces = self.face_detector.detectMultiScale(
            gray,
            scaleFactor=1.1,
            minNeighbors=5,
            minSize=(60, 60)
        )

        if len(faces) == 0:

            raise ValueError(
                "No face detected. Try a clearer, "
                "well-lit image."
            )

        # Select the largest detected face.
        x, y, w, h = max(
            faces,
            key=lambda box: box[2] * box[3]
        )

        face_bgr = frame[y:y + h, x:x + w]

        # ==================================
        # READ MODEL INPUT DIMENSIONS
        # ==================================

        input_shape = self.model.input_shape

        height = input_shape[1] or IMAGE_SIZE
        width = input_shape[2] or IMAGE_SIZE
        channels = input_shape[3]

        height = int(height)
        width = int(width)

        # ==================================
        # PREPARE FACE IMAGE
        # ==================================

        if channels == 1:

            face = cv2.cvtColor(
                face_bgr,
                cv2.COLOR_BGR2GRAY
            )

        else:

            face = cv2.cvtColor(
                face_bgr,
                cv2.COLOR_BGR2RGB
            )

        face = cv2.resize(
            face,
            (width, height)
        )

        if channels == 1:

            face = np.expand_dims(
                face,
                axis=-1
            )

        face = face.astype(np.float32)

        # IMPORTANT:
        # This assumes the trained model contains a
        # Rescaling(1./255) layer.
        # Training and prediction preprocessing must match.

        face = np.expand_dims(
            face,
            axis=0
        )

        # ==================================
        # RUN AI PREDICTION
        # ==================================

        predictions = self.model.predict(
            face,
            verbose=0
        )

        probabilities = np.asarray(
            predictions,
            dtype=np.float32
        ).reshape(-1)

        # ==================================
        # VALIDATE PREDICTIONS
        # ==================================

        if len(probabilities) != len(LABELS):

            raise ValueError(
                "The AI model must return exactly five "
                "emotion probabilities."
            )

        if not np.all(np.isfinite(probabilities)):

            raise ValueError(
                "The AI model returned invalid prediction values."
            )

        if np.any(probabilities < 0):

            raise ValueError(
                "The AI model returned negative probabilities."
            )

        if not np.isclose(
            np.sum(probabilities),
            1.0,
            atol=0.01
        ):

            raise ValueError(
                "The model outputs are not valid probabilities. "
                "Check the final softmax layer."
            )

        # ==================================
        # IDENTIFY PREDICTED EMOTION
        # ==================================

        index = int(
            np.argmax(probabilities)
        )

        emotion = LABELS[index]
        emoji = EMOJIS[emotion]

        confidence = float(
            probabilities[index] * 100
        )

        # ==================================
        # RETURN RESULT TO FLASK
        # ==================================

        return {
            "emotion": emotion,
            "emoji": emoji,
            "confidence": round(confidence, 2)
        }
