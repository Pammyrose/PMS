param(
    [string]$SourcePath = (Join-Path $PSScriptRoot '..\docs\PMS_User_Manual.md'),
    [string]$OutputPath = (Join-Path $PSScriptRoot '..\docs\PMS_User_Manual.docx'),
    [string]$DocumentTitle = 'DENR-CAR PMS User Manual',
    [string]$DocumentSubject = 'Operating instructions for PMS users',
    [string]$HeaderLabel = 'DENR-CAR PMS | User Manual | Version 1.0 - Draft',
    [string]$SubtitleText = 'User Manual',
    [switch]$Landscape,
    [switch]$CompactHeadings
)

$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

function Escape-Xml([string]$Value) {
    return [System.Security.SecurityElement]::Escape($Value)
}

function New-Paragraph([string]$Value, [string]$Style = 'Normal') {
    $paragraphProperties = '<w:pStyle w:val="' + $Style + '"/>'
    if ($Style -eq 'Screenshot') {
        $paragraphProperties += '<w:shd w:fill="EEF4F0"/><w:spacing w:before="180" w:after="180"/><w:pBdr><w:top w:val="single" w:sz="4" w:color="A5BBAF"/><w:left w:val="single" w:sz="4" w:color="A5BBAF"/><w:bottom w:val="single" w:sz="4" w:color="A5BBAF"/><w:right w:val="single" w:sz="4" w:color="A5BBAF"/></w:pBdr>'
    }
    return '<w:p><w:pPr>' + $paragraphProperties + '</w:pPr><w:r><w:t xml:space="preserve">' + (Escape-Xml $Value) + '</w:t></w:r></w:p>'
}

function New-Table([string[]]$Lines) {
    $rows = [System.Collections.Generic.List[string[]]]::new()
    foreach ($line in $Lines) {
        if ($line -match '^\|[\s:|\-]+\|$') { continue }
        [string[]]$cells = $line.Trim().Trim('|').Split('|') | ForEach-Object { $_.Trim() }
        $rows.Add($cells)
    }
    $columnCount = $rows[0].Length
    $tableWidth = if ($Landscape) { 14570 } else { 9638 }
    $width = [int][Math]::Floor($tableWidth / $columnCount)
    $isTimeline = $rows[0][0] -eq 'Activity / Month'
    $columnWidths = @()
    for ($column = 0; $column -lt $columnCount; $column++) {
        if ($isTimeline) {
            $columnWidths += $(if ($column -eq 0) { 3300 } else { [int][Math]::Floor(($tableWidth - 3300) / ($columnCount - 1)) })
        } else { $columnWidths += $width }
    }
    $table = [System.Text.StringBuilder]::new()
    [void]$table.Append('<w:tbl><w:tblPr><w:tblW w:w="9638" w:type="dxa"/><w:tblLayout w:type="fixed"/><w:tblBorders><w:top w:val="single" w:sz="4" w:color="CAD6CD"/><w:left w:val="single" w:sz="4" w:color="CAD6CD"/><w:bottom w:val="single" w:sz="4" w:color="CAD6CD"/><w:right w:val="single" w:sz="4" w:color="CAD6CD"/><w:insideH w:val="single" w:sz="4" w:color="CAD6CD"/><w:insideV w:val="single" w:sz="4" w:color="CAD6CD"/></w:tblBorders><w:tblCellMar><w:top w:w="90" w:type="dxa"/><w:left w:w="100" w:type="dxa"/><w:bottom w:w="90" w:type="dxa"/><w:right w:w="100" w:type="dxa"/></w:tblCellMar></w:tblPr><w:tblGrid>')
    for ($column = 0; $column -lt $columnCount; $column++) { [void]$table.Append('<w:gridCol w:w="' + $columnWidths[$column] + '"/>') }
    [void]$table.Append('</w:tblGrid>')
    for ($rowIndex = 0; $rowIndex -lt $rows.Count; $rowIndex++) {
        $rowProperties = '<w:cantSplit/>'
        if ($rowIndex -eq 0) { $rowProperties += '<w:tblHeader/>' }
        [void]$table.Append('<w:tr><w:trPr>' + $rowProperties + '</w:trPr>')
        $cellIndex = 0
        foreach ($cell in $rows[$rowIndex]) {
            $fill = if ($rowIndex -eq 0) { 'DCE9DF' } elseif ($rowIndex % 2 -eq 0) { 'F5F7F5' } else { 'FFFFFF' }
            if ($isTimeline -and $rowIndex -gt 0 -and $cell -eq 'R') { $fill = '00D7DA' }
            $style = if ($rowIndex -eq 0) { 'TableHeader' } else { 'TableText' }
            $cellParagraph = New-Paragraph $cell $style
            if ($isTimeline -and $cellIndex -gt 0) { $cellParagraph = $cellParagraph.Replace('<w:pPr>', '<w:pPr><w:jc w:val="center"/>') }
            [void]$table.Append('<w:tc><w:tcPr><w:tcW w:w="' + $columnWidths[$cellIndex] + '" w:type="dxa"/><w:shd w:fill="' + $fill + '"/><w:vAlign w:val="top"/></w:tcPr>' + $cellParagraph + '</w:tc>')
            $cellIndex++
        }
        [void]$table.Append('</w:tr>')
    }
    [void]$table.Append('</w:tbl>')
    return $table.ToString().Replace('<w:tblW w:w="9638"', '<w:tblW w:w="' + $tableWidth + '"')
}

$lines = [System.IO.File]::ReadAllLines([System.IO.Path]::GetFullPath($SourcePath))
$body = [System.Text.StringBuilder]::new()
$firstHeading = $true
for ($index = 0; $index -lt $lines.Length; $index++) {
    $line = $lines[$index].Trim()
    if ($line.Length -eq 0) { continue }
    if ($line -eq '<!-- pagebreak -->') {
        if ($CompactHeadings) { [void]$body.Append('<w:p><w:r><w:br w:type="page"/></w:r></w:p>') }
        continue
    }
    if ($line.StartsWith('|')) {
        $tableLines = [System.Collections.Generic.List[string]]::new()
        while ($index -lt $lines.Length -and $lines[$index].Trim().StartsWith('|')) {
            $tableLines.Add($lines[$index].Trim())
            $index++
        }
        $index--
        [void]$body.Append((New-Table $tableLines.ToArray()))
        [void]$body.Append((New-Paragraph ''))
    } elseif ($line.StartsWith('# ')) {
        $style = if ($firstHeading) { 'Title' } else { 'Heading1' }
        [void]$body.Append((New-Paragraph $line.Substring(2) $style))
        $firstHeading = $false
    } elseif ($line.StartsWith('## ')) {
        $style = if ($line.Substring(3) -eq $SubtitleText) { 'Subtitle' } else { 'Heading2' }
        [void]$body.Append((New-Paragraph $line.Substring(3) $style))
    } elseif ($line.StartsWith('[Screenshot ')) {
        [void]$body.Append((New-Paragraph $line.Trim('[', ']') 'Screenshot'))
        [void]$body.Append((New-Paragraph 'Insert screenshot here.' 'Screenshot'))
    } elseif ($line -match '^\d+\. ') {
        [void]$body.Append((New-Paragraph $line 'Step'))
    } elseif ($line.StartsWith('- ')) {
        [void]$body.Append((New-Paragraph ('- ' + $line.Substring(2)) 'Step'))
    } else {
        [void]$body.Append((New-Paragraph $line))
    }
}

$document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><w:body>' + $body.ToString() + '<w:sectPr><w:headerReference w:type="default" r:id="rIdHeader"/><w:footerReference w:type="default" r:id="rIdFooter"/><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134" w:header="567" w:footer="567"/><w:titlePg/></w:sectPr></w:body></w:document>'

$styles = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="22"/><w:lang w:val="en-PH"/></w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="120" w:line="276" w:lineRule="auto"/></w:pPr></w:pPrDefault></w:docDefaults>
<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/></w:style>
<w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:basedOn w:val="Normal"/><w:pPr><w:spacing w:before="2000" w:after="300"/><w:keepNext/></w:pPr><w:rPr><w:b/><w:color w:val="24543A"/><w:sz w:val="48"/></w:rPr></w:style>
<w:style w:type="paragraph" w:styleId="Subtitle"><w:name w:val="Subtitle"/><w:basedOn w:val="Normal"/><w:pPr><w:spacing w:after="600"/><w:keepNext/></w:pPr><w:rPr><w:color w:val="47624F"/><w:sz w:val="34"/></w:rPr></w:style>
<w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:pPr><w:keepNext/><w:pageBreakBefore/><w:spacing w:before="0" w:after="240"/><w:outlineLvl w:val="0"/></w:pPr><w:rPr><w:b/><w:color w:val="24543A"/><w:sz w:val="32"/></w:rPr></w:style>
<w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:pPr><w:keepNext/><w:spacing w:before="240" w:after="120"/><w:outlineLvl w:val="1"/></w:pPr><w:rPr><w:b/><w:color w:val="345943"/><w:sz w:val="25"/></w:rPr></w:style>
<w:style w:type="paragraph" w:styleId="Step"><w:name w:val="Procedure Step"/><w:basedOn w:val="Normal"/><w:pPr><w:ind w:left="300" w:hanging="300"/><w:spacing w:after="90"/></w:pPr></w:style>
<w:style w:type="paragraph" w:styleId="Screenshot"><w:name w:val="Screenshot Placeholder"/><w:basedOn w:val="Normal"/><w:pPr><w:keepNext/><w:spacing w:after="160"/></w:pPr><w:rPr><w:i/><w:color w:val="48634F"/><w:sz w:val="20"/></w:rPr></w:style>
<w:style w:type="paragraph" w:styleId="TableText"><w:name w:val="Table Text"/><w:basedOn w:val="Normal"/><w:pPr><w:spacing w:after="40" w:line="240" w:lineRule="auto"/></w:pPr><w:rPr><w:sz w:val="20"/></w:rPr></w:style>
<w:style w:type="paragraph" w:styleId="TableHeader"><w:name w:val="Table Header"/><w:basedOn w:val="TableText"/><w:rPr><w:b/><w:color w:val="24543A"/><w:sz w:val="20"/></w:rPr></w:style>
</w:styles>
'@

if ($CompactHeadings) { $styles = $styles.Replace('<w:pageBreakBefore/>', '') }
if ($Landscape) {
    $document = $document.Replace('<w:pgSz w:w="11906" w:h="16838"/>', '<w:pgSz w:w="16838" w:h="11906" w:orient="landscape"/>')
    $styles = $styles.Replace('w:before="2000"', 'w:before="360"').Replace('w:after="600"', 'w:after="240"')
}

$contentTypes = '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/><Override PartName="/word/header1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml"/><Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/><Override PartName="/word/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/></Types>'
$rootRels = '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/></Relationships>'
$docRels = '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/><Relationship Id="rIdHeader" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header1.xml"/><Relationship Id="rIdFooter" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/><Relationship Id="rIdSettings" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings" Target="settings.xml"/></Relationships>'
$header = '<?xml version="1.0" encoding="UTF-8"?><w:hdr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:p><w:pPr><w:pBdr><w:bottom w:val="single" w:sz="4" w:color="A5BBAF"/></w:pBdr></w:pPr><w:r><w:rPr><w:color w:val="47624F"/><w:sz w:val="18"/></w:rPr><w:t>DENR-CAR PMS | User Manual | Version 1.0 - Draft</w:t></w:r></w:p></w:hdr>'
$footer = '<?xml version="1.0" encoding="UTF-8"?><w:ftr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:p><w:pPr><w:jc w:val="right"/></w:pPr><w:r><w:rPr><w:sz w:val="18"/></w:rPr><w:t xml:space="preserve">Page </w:t></w:r><w:fldSimple w:instr="PAGE"><w:r><w:t>1</w:t></w:r></w:fldSimple></w:p></w:ftr>'
$settings = '<?xml version="1.0" encoding="UTF-8"?><w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:updateFields w:val="true"/><w:compat/></w:settings>'
$core = '<?xml version="1.0" encoding="UTF-8"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:title>DENR-CAR PMS User Manual</dc:title><dc:subject>Operating instructions for PMS users</dc:subject><dc:creator>DENR-CAR</dc:creator><dc:description>Documentation draft with screenshot placeholders.</dc:description></cp:coreProperties>'
$header = $header.Replace('DENR-CAR PMS | User Manual | Version 1.0 - Draft', (Escape-Xml $HeaderLabel))
$core = $core.Replace('DENR-CAR PMS User Manual', (Escape-Xml $DocumentTitle)).Replace('Operating instructions for PMS users', (Escape-Xml $DocumentSubject)).Replace('Documentation draft with screenshot placeholders.', 'Documentation draft for review.')

$parts = [ordered]@{
    '[Content_Types].xml' = $contentTypes
    '_rels/.rels' = $rootRels
    'word/document.xml' = $document
    'word/styles.xml' = $styles
    'word/_rels/document.xml.rels' = $docRels
    'word/header1.xml' = $header
    'word/footer1.xml' = $footer
    'word/settings.xml' = $settings
    'docProps/core.xml' = $core
}

$resolvedOutput = [System.IO.Path]::GetFullPath($OutputPath)
if (Test-Path -LiteralPath $resolvedOutput) { throw "Output already exists; choose a new OutputPath to preserve it: $resolvedOutput" }
$file = [System.IO.FileStream]::new($resolvedOutput, [System.IO.FileMode]::CreateNew, [System.IO.FileAccess]::ReadWrite)
try {
    $archive = [System.IO.Compression.ZipArchive]::new($file, [System.IO.Compression.ZipArchiveMode]::Create, $true)
    try {
        foreach ($part in $parts.GetEnumerator()) {
            # Parse each part before packaging so malformed XML cannot be emitted.
            $null = [xml]$part.Value
            $entry = $archive.CreateEntry($part.Key)
            $writer = [System.IO.StreamWriter]::new($entry.Open(), [System.Text.UTF8Encoding]::new($false))
            try { $writer.Write($part.Value) } finally { $writer.Dispose() }
        }
    } finally { $archive.Dispose() }
} finally { $file.Dispose() }

Write-Output "Created: $resolvedOutput"
Write-Output "Package parts: $($parts.Count)"
