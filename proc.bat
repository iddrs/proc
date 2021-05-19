@echo off

echo "Iniciando"
git pull

cd cmd

powershell.exe -NoLogo

cd ..

echo "Finalizando"
git add .
git commit -m "Auto-commit"
git push
pause
exit