# EmojiAI missing project sections

Copy the folders into your existing `ITPROJECT` directory. Do not replace
your existing `css/style.css`, `includes/head.php`, or `includes/navbar.php`.

## 1. PHP database connection
- Put `includes/db.php` in your existing includes folder.
- Edit the database name in that file to match phpMyAdmin.
- The file creates a mysqli connection named `$conn`.
- Check your real table and column names before using queries in other pages.

## 2. Capture styling
- Put `css/capture.css` in your css folder.
- In capture.php set `$pageStyles = ["css/capture.css"];` before including head.php.
- The CSS expects the IDs/classes from the current capture.php: `.capture-page`,
  `.capture-container`, `.capture-header`, `.camera-container`, `#video`,
  `#camera-status`, `#capture`, `.result-container`, `#result-emoji`,
  `#result-emotion`, and `#result-confidence`.

## 3. Gallery JavaScript
- Put `js/gallery.js` in your js folder.
- Include it on stickers.php or gallery.php just before `</body>`.
- Add `id="gallerySearch"` to the search field and `id="galleryFilter"` to
  the type selector. Each card should use class `gallery-item` and data-type
  of `png` or `gif`. Add `id="galleryEmpty"` to the no-results message.

## 4. Python backend
From PowerShell:
    cd "C:\\Users\\Phenyo Thena\\Documents\\XAMMP\\htdocs\\ITPROJECT\\backend"
    py -m venv .venv
    .venv\\Scripts\\Activate.ps1
    python -m pip install -r requirements.txt
    python app.py

If `py` is not recognized, install Python from python.org and select
"Add Python to PATH", then reopen PowerShell.

Test:
    http://127.0.0.1:5000/

Important: emotion_model.py is a model adapter. It requires a trained
`backend/models/emotion_model.keras` file. It does not invent predictions.
Until that model exists, `/detect` returns MODEL_NOT_CONFIGURED (503).

## 5. Upload directory
The uploads folder is for generated PNG/GIF assets. Ensure PHP can write to it.
Keep the .htaccess file on Apache and do not allow script uploads.

## 6. Android keyboard
This is a separate native Android Studio project starter. The web app cannot
install stickers directly into a phone keyboard. Android keyboard image insertion
requires a native InputMethodService and support from the receiving app.
