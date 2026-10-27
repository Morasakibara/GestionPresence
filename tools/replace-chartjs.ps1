$old = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js'
$new = "{{ asset('js/chart.umd.min.js') }}"
$files = Get-ChildItem -Recurse resources/views -Filter *.blade.php | Select-String -SimpleMatch $old | Select-Object -ExpandProperty Path | Sort-Object -Unique
foreach ($f in $files) {
    $c = Get-Content -Raw -LiteralPath $f
    $c = $c.Replace($old, $new)
    Set-Content -LiteralPath $f -NoNewline -Value $c
}
Write-Output "Replaced in $($files.Count) files"
