@echo off
echo ========================================
echo    SkillBridge - Demarrage Rapide
echo ========================================
echo.

REM Verifier si XAMPP est installe
if exist "C:\xampp\xampp-control.exe" (
    echo [OK] XAMPP detecte
    echo.
    echo Demarrage de XAMPP...
    start "" "C:\xampp\xampp-control.exe"
    timeout /t 3 /nobreak >nul
    echo.
    echo ========================================
    echo  ETAPES SUIVANTES :
    echo ========================================
    echo.
    echo 1. Dans XAMPP Control Panel :
    echo    - Cliquez sur "Start" pour Apache
    echo    - Cliquez sur "Start" pour MySQL
    echo.
    echo 2. Importez la base de donnees :
    echo    - Ouvrez http://localhost/phpmyadmin
    echo    - Creez la base "skillbridge"
    echo    - Importez les fichiers SQL dans cet ordre :
    echo      * produit.sql
    echo      * users.sql
    echo      * commande.sql
    echo      * chat_messages.sql
    echo.
    echo 3. Accedez a l'application :
    echo    - http://localhost/Esprit-PW-2A32-2526-SkillBridge-louay-produit/Esprit-PW-2A32-2526-SkillBridge-louay-produit/
    echo.
    echo ========================================
    echo  COMPTES DE TEST :
    echo ========================================
    echo.
    echo Admin  : admin@skillbridge.com / admin123
    echo Client : ahmed@test.com / admin123
    echo Vendeur: fatma@test.com / admin123
    echo.
    echo ========================================
    pause
) else (
    echo [ERREUR] XAMPP n'est pas installe dans C:\xampp\
    echo.
    echo Veuillez :
    echo 1. Installer XAMPP depuis https://www.apachefriends.org/
    echo 2. Ou demarrer XAMPP manuellement
    echo 3. Puis relancer ce script
    echo.
    pause
)
