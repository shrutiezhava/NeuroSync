@echo off
echo 🔁 Starting Flask server...
start "" cmd /c "python app.py"

timeout /t 2 >nul

echo 🌐 Opening browser...
start "" "http://localhost/prj/Neurona/Neuro/youtube/index.php"
