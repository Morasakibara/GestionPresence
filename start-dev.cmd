@echo off
rem Démarre Mailpit + le serveur Laravel (nécessite Git Bash)
cd /d "%~dp0"
if exist "C:\Program Files\Git\bin\bash.exe" (
    "C:\Program Files\Git\bin\bash.exe" start-dev.sh
) else (
    echo Git Bash introuvable. Lancez manuellement : bash start-dev.sh
    pause
)
