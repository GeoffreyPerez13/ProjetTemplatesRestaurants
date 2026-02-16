@echo off
chcp 65001 > nul
title MailHog - ProjetTemplatesRestaurants

echo.
echo ========================================
echo   MAILHOG - SERVEUR DE TEST
echo ========================================
echo Projet: ProjetTemplatesRestaurants
echo Interface: http://localhost:8025
echo SMTP: localhost:1025
echo ========================================
echo.

echo Demarrage de MailHog...
start "MailHog" tools\MailHog.exe

timeout /t 2 /nobreak > nul

echo Ouverture de l'interface web...
start http://localhost:8025

echo.
echo MailHog est en cours d'execution!
echo Pour arreter: Fermez la fenetre MailHog
echo.
pause