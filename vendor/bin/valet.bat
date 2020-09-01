@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../cretueusebiu/valet-windows/valet
php "%BIN_TARGET%" %*
