@echo off

echo "Iniciando"
git pull

powershell.exe -NoLogo

echo "Finalizando"
git add .
git commit -m "Auto-commit"
git push
exit