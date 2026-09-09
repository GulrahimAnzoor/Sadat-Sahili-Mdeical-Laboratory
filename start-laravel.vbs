Set WshShell = CreateObject("WScript.Shell")

WshShell.Run "cmd /c cd /d ""D:\Sadat-Sahili-Mdeical-Laboratory"" && php artisan serve", 0, False

WScript.Sleep 3000
WshShell.Run "http://127.0.0.1:8000", 1, False
