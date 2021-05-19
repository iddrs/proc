@echo off

echo "Iniciando"
git pull

powershell.exe -NoLogo cmd/

echo "Finalizando"
git add .
git commit -m "Auto-commit"
git push
pause
exit