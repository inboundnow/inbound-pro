tx.exe pull -a --skip


for %%a in (*.po) do (
   if /i not "%%~na"=="inbound-email" (
        msgfmt -cv -o "inbound-email=-%%~na.mo" "%%a"
        del "%%a"
    )
)

PAUSE