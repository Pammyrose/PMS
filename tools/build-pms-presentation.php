<?php

// Builds an editable PowerPoint and an HTML preview without changing application dependencies.
$root = dirname(__DIR__);
$out = $root.'/docs';
$slides = [];
$navy = '17365D';
$teal = '159B94';
$blue = '2146B5';
$ink = '213547';
$muted = '64748B';

function slide(string $title, string $subtitle, string $notes): int
{
    global $slides;
    $slides[] = ['title' => $title, 'subtitle' => $subtitle, 'notes' => $notes, 'items' => []];
    return count($slides) - 1;
}
function box(int $s, float $x, float $y, float $w, float $h, string $text = '', string $fill = 'FFFFFF', string $color = '213547', int $size = 20, bool $bold = false, string $align = 'l', string $kind = 'roundRect'): void
{
    global $slides;
    $slides[$s]['items'][] = compact('x', 'y', 'w', 'h', 'text', 'fill', 'color', 'size', 'bold', 'align', 'kind');
}
function text(int $s, float $x, float $y, float $w, float $h, string $text, int $size = 20, string $color = '213547', bool $bold = false): void
{
    box($s, $x, $y, $w, $h, $text, 'none', $color, $size, $bold, 'l', 'rect');
}
function card(int $s, float $x, float $y, float $w, float $h, string $title, string $body, string $accent = '159B94'): void
{
    box($s, $x, $y, $w, $h, '', 'FFFFFF');
    box($s, $x, $y, .07, $h, '', $accent, kind: 'rect');
    text($s, $x + .2, $y + .18, $w - .4, .55, $title, 21, $accent, true);
    text($s, $x + .2, $y + .88, $w - .4, $h - 1.04, $body, 19);
}
function arrow(int $s, float $x, float $y, float $w = .38, float $h = .26, string $kind = 'rightArrow'): void
{
    box($s, $x, $y, $w, $h, '', '94A3B8', kind: $kind);
}

$s = slide('Performance Monitoring System', 'DENR-CAR  |  Complete process and user workflow', 'Introduce PMS as a shared system for monitoring physical and financial targets and accomplishments. This presentation reflects the current workspace, including the revised non-cumulative quarter and annual rules.');
box($s, .55, 1.85, 12.2, 3.65, '', $navy);
text($s, .95, 2.25, 10.9, 1.5, "From office data entry\nto consolidated performance reports", 34, 'FFFFFF', true);
text($s, .95, 4.25, 10.8, .7, 'Login  /  Permissions  /  Entry  /  Approval  /  Reporting', 21, 'B9E8E4');
text($s, .7, 6.0, 11.7, .5, 'System walkthrough  •  7 October 2026', 17, $muted);

$s = slide('What PMS brings together', 'One reporting structure for physical and financial performance', 'Explain that every value belongs to a sector, reporting year, PAP, indicator and office. Physical performance measures outputs. Financial performance covers allotment, obligation, disbursement and utilization.');
card($s, .6, 1.95, 3.85, 3.35, 'Physical performance', "Targets and accomplishments\nMonthly, quarterly and annual values\nIndicator-specific calculation rules");
card($s, 4.73, 1.95, 3.85, 3.35, 'Financial performance', "Allotment, obligation and disbursement\nBudget utilization ratios\nOffice and program summaries", $blue);
card($s, 8.86, 1.95, 3.85, 3.35, 'Reporting scope', "Sector and reporting year\nProgram / Activity / Project (PAP)\nIndicator and responsible office", $navy);
text($s, .7, 5.75, 11.9, .7, '11 sectors: GASS, STO, ENF, PA, ENGP, LANDS, SOILCON, NRA, PARIA, COBB and Continuing.', 19);

$s = slide('The complete process at a glance', 'Follow the record from login to reporting', 'Walk across the first row, then down and back across the second row. Approval is required only for office-user corrections to locked accomplishment months. The next slides explain each stage.');
$stages = ['1  Sign in', '2  Check role\nand office', '3  Select sector\nand year', '4  Enter or\nupdate data', '8  Monitor and\nexport reports', '7  Reload and\nverify values', '6  Save or route\nfor approval', '5  Calculate and\nvalidate'];
foreach ($stages as $i => $label) {
    $row = intdiv($i, 4); $col = $i % 4;
    box($s, .65 + $col * 3.15, 2.05 + $row * 1.8, 2.65, 1.15, str_replace('\\n', "\n", $label), $row ? $navy : $teal, 'FFFFFF', 20, true, 'ctr');
}
for ($i = 0; $i < 3; $i++) { arrow($s, 3.35 + $i * 3.15, 2.5); arrow($s, 3.35 + $i * 3.15, 4.3, kind: 'leftArrow'); }
arrow($s, 11.3, 3.35, .3, .35, 'downArrow');
text($s, .75, 5.55, 11.7, .9, 'After verification, users can continue editing, review request status, generate reports or log out.', 22);

$s = slide('Who can do what?', 'Office coverage and editing permission are checked separately', 'PENRO dashboard, export and history coverage may include its service area, but entry stays scoped to the assigned office. CENRO and designated office users cannot edit targets. PENRO cannot approve or decline requests. Administrator and Regional Office can review locked corrections.');
card($s, .65, 1.9, 5.85, 2.15, 'Administrator', "Manage accounts, PAPs, indicators and imports.\nMaintain records and review locked corrections.", $blue);
card($s, 6.8, 1.9, 5.85, 2.15, 'Regional Office', "Monitor consolidated performance and maintain\nauthorized accomplishments. Review corrections.", $blue);
card($s, .65, 4.3, 5.85, 2.15, 'PENRO', "Enter assigned-office targets and accomplishments.\nTrack requests; view permitted provincial coverage.");
card($s, 6.8, 4.3, 5.85, 2.15, 'CENRO / designated user', "Enter assigned-office accomplishments.\nTargets are read-only; track own correction requests.");

$s = slide('Sign in and choose the reporting scope', 'Check the context before reading or entering any values', 'Failed login attempts show an error and repeated failures are temporarily limited. After login, the dashboard and sector pages apply role and office restrictions. Choose the reporting year before entering data.');
$labels = ['Sign in', 'Dashboard', 'Office coverage', 'Sector', 'Reporting year'];
foreach ($labels as $i => $label) {
    box($s, .65 + $i * 2.55, 2.25, 2.05, 1.1, $label, $i === 0 ? $blue : $teal, 'FFFFFF', 20, true, 'ctr');
    if ($i < 4) arrow($s, 2.75 + $i * 2.55, 2.68);
}
card($s, .7, 4.05, 5.8, 2.1, 'Check your account', 'Confirm your role and office assignment.\nAvailable actions depend on both.', $blue);
card($s, 6.85, 4.05, 5.8, 2.1, 'Check the selected records', 'Confirm sector, year, PAP, indicator and unit\nof measurement before entering values.');

$s = slide('Prepare records and enter performance data', 'Administrator setup supports office reporting', 'Administrator account and PAP management are distinct from office data entry. Supported Excel imports use preview and confirmation. Users then open the relevant physical or financial columns, enter permitted monthly values and save. Column visibility changes presentation, not permissions.');
card($s, .65, 1.95, 5.85, 3.9, 'Administrator preparation', "Create or update accounts and office assignments.\nMaintain PAP hierarchy and indicators.\nSet indicator types and reporting year.\nPreview and confirm supported Excel imports.", $blue);
card($s, 6.8, 1.95, 5.85, 3.9, 'Office data entry', "Locate the PAP, indicator and office row.\nShow Physical or Financial columns and months.\nEnter authorized monthly values and remarks.\nCheck calculated totals, then select Save.");
text($s, .75, 6.15, 11.8, .45, 'Showing a column does not grant permission to edit it.', 18, $muted);

$s = slide('How physical totals are calculated', 'Quarterly and annual rules depend on the indicator type', 'These are the current office-level physical rules. Cumulative quarters sum months and annual sums quarters. Non-cumulative quarters use the most frequent month and annual uses the most frequent quarter. Semi-cumulative quarters sum months and annual uses the highest quarter. Financial values remain additive. Monthly source values are retained.');
card($s, .65, 1.9, 3.85, 3.4, 'Cumulative', "Quarter: sum of 3 months\nAnnual: sum of 4 quarters\n\nExample quarter:\n2 + 3 + 4 = 9");
card($s, 4.75, 1.9, 3.85, 3.4, 'Non-cumulative', "Quarter: most frequent month\nAnnual: most frequent quarter\n\nExample quarter:\n11, 11, 69 → 11", $blue);
card($s, 8.85, 1.9, 3.85, 3.4, 'Semi-cumulative', "Quarter: sum of 3 months\nAnnual: highest quarter\n\nExample annual:\n9, 12, 6, 8 → 12", $navy);
box($s, .75, 5.65, 11.85, .8, 'Financial totals use sums. An unusual monthly value stays in its month even when the repeated value determines the quarter.', 'E1F3F0', $ink, 18);

$s = slide('Saving: direct update or correction request?', 'Office-user changes to locked accomplishments follow a review path', 'Current and future accomplishment months save directly for office users. A locked-month correction requires a reason and goes to Administrator or Regional Office review. Those reviewers can directly edit locked accomplishments. Validation and office authorization happen before records are written. A failed save does not confirm an edit.');
box($s, .65, 2.0, 2.5, 1.05, 'Click Save', $blue, 'FFFFFF', 22, true, 'ctr');
arrow($s, 3.22, 2.4);
box($s, 3.7, 2.0, 3.2, 1.05, 'Validate values\nand office permission', $teal, 'FFFFFF', 20, true, 'ctr');
arrow($s, 7.02, 2.4);
box($s, 7.6, 1.9, 4.9, 1.25, 'Locked accomplishment change\nby an office user?', $navy, 'FFFFFF', 21, true, 'ctr');
card($s, .65, 3.9, 3.8, 2.35, 'Invalid or unauthorized', "Show the error.\nCorrect entries and retry.", 'D84343');
card($s, 4.7, 3.9, 3.8, 2.35, 'No → save directly', "Write confirmed values.\nShow the save result.");
card($s, 8.75, 3.9, 3.8, 2.35, 'Yes → request review', "Enter a correction reason.\nSubmit a pending request.", $blue);

$s = slide('Locked correction: review and decision', 'The existing confirmed value stays in place while review is pending', 'Review the submitting user, office, sector, PAP, indicator, year, changed months, proposed values and reason. Approving applies the correction and recalculates totals. Declining keeps existing values and requires review notes. Users can track pending, approved and declined status. PENRO is not a reviewer.');
box($s, .7, 1.95, 3.5, 1.0, 'Office submits correction\nwith a reason', $blue, 'FFFFFF', 20, true, 'ctr');
arrow($s, 4.35, 2.32);
box($s, 4.95, 1.95, 3.3, 1.0, 'Pending request', 'FFF1CE', $ink, 21, true, 'ctr');
arrow($s, 8.4, 2.32);
box($s, 8.95, 1.95, 3.6, 1.0, 'Administrator / RO\nreviews supporting records', $navy, 'FFFFFF', 19, true, 'ctr');
card($s, .7, 3.65, 5.8, 2.5, 'Approved', "Apply the corrected accomplishment.\nRecalculate quarterly and annual totals.\nUser checks the updated record.");
card($s, 6.85, 3.65, 5.8, 2.5, 'Declined', "Keep the existing confirmed value.\nRecord the reviewer’s explanation.\nUser reviews notes before revising.", 'D84343');

$s = slide('Reload, notifications and change history', 'Verify that the intended result is confirmed', 'A visible edit alone is not proof that it has been saved. After a successful save, reload the same sector and year and compare the month, quarter, annual and aggregate totals. Pending corrections remain pending until approved. History is available to authorized roles and provides traceability; it does not undo a change.');
card($s, .65, 2.0, 3.85, 3.8, 'Reload verification', "Confirm the save result.\nReload the same sector and year.\nCheck the edited month.\nCompare quarter, annual and\nprovince / CAR totals.");
card($s, 4.75, 2.0, 3.85, 3.8, 'Notifications', "Pending: awaiting review.\nApproved: correction applied.\nDeclined: correction not applied.\nRead the reviewer’s notes.\nReopen approved records.", $blue);
card($s, 8.85, 2.0, 3.85, 3.8, 'Activity history', "Authorized roles can inspect\nrecorded changes.\nUse history to trace edits.\nCorrect values through the\nnormal entry or review process.", $navy);

$s = slide('Monitor performance and generate reports', 'Use confirmed records within the selected reporting scope', 'The dashboard filters by office, sector and year within the user’s permitted access. Review physical and financial performance and delays. Excel exports follow the selected sector/year and role-based office coverage. Screen search or hidden columns do not necessarily filter the export. Save and verify data before exporting.');
box($s, .65, 2.0, 3.5, 1.05, 'Saved performance records', $teal, 'FFFFFF', 20, true, 'ctr');
arrow($s, 4.3, 2.4);
box($s, 4.95, 2.0, 3.35, 1.05, 'Dashboard and summaries', $blue, 'FFFFFF', 20, true, 'ctr');
arrow($s, 8.45, 2.4);
box($s, 9.05, 2.0, 3.55, 1.05, 'Generate Excel report', $navy, 'FFFFFF', 20, true, 'ctr');
card($s, .7, 3.95, 5.8, 2.25, 'Monitor', "Check office, sector, year and reporting period.\nReview physical outputs, financial utilization\nand delayed accomplishments.");
card($s, 6.85, 3.95, 5.8, 2.25, 'Export', "Save and verify source values first.\nGenerate the sector report.\nCheck office coverage and totals in Excel.", $blue);

$s = slide('Walkthrough: an unusual monthly value', 'ABRA example using the revised non-cumulative rules', 'Use this example to demonstrate the table calculation. July and August are 11; September is 69. The quarter uses the repeated monthly value 11. If all four quarters are 11, the annual value is 11. September remains 69. After saving, reload and confirm the office, province, CAR and export results.');
$columns = ['JUL', 'AUG', 'SEP', 'Q3', 'ANNUAL'];
$values = ['11', '11', '69', '11', '11'];
foreach ($columns as $i => $label) {
    $x = .75 + $i * 2.52;
    box($s, $x, 2.05, 2.25, .7, $label, $i < 3 ? $teal : ($i === 3 ? 'EF4444' : $navy), 'FFFFFF', 20, true, 'ctr', 'rect');
    box($s, $x, 2.85, 2.25, 1.0, $values[$i], 'FFFFFF', $blue, 32, true, 'ctr');
}
text($s, .85, 4.25, 11.8, .9, 'Monthly source: 11, 11, 69  →  most frequent value: 11  →  Q3: 11', 24, $blue, true);
text($s, .85, 5.2, 11.8, .9, 'Four quarters: 11, 11, 11, 11  →  annual total: 11', 24, $ink, true);

$s = slide('A complete reporting cycle', 'Use the same sequence for every sector and reporting year', 'Close by repeating the process. Setup and access establish who can enter which records. Monthly values drive calculations. Save results must be confirmed, and locked corrections need a decision before becoming official. Monitoring and Excel reports use the confirmed records. End with questions or a live demonstration.');
card($s, .7, 1.95, 5.8, 3.75, 'Before entry', "Confirm your account and assigned office.\nSelect the sector and reporting year.\nIdentify the PAP, indicator and unit.\nUse verified source records.", $blue);
card($s, 6.85, 1.95, 5.8, 3.75, 'After entry', "Check calculations and the save result.\nTrack locked-month correction decisions.\nReload and verify confirmed values.\nReview summaries, export and log out.");
text($s, .85, 6.1, 11.8, .5, 'Questions and live demonstration', 24, $navy, true);

function xml(string $v): string { return htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8'); }
function emu(float $n): int { return (int) round($n * 914400); }
function shapeXml(array $o, int $id): string
{
    $fill = $o['fill'] === 'none' ? '<a:noFill/>' : '<a:solidFill><a:srgbClr val="'.$o['fill'].'"/></a:solidFill>';
    $paragraphs = '';
    foreach (explode("\n", $o['text']) as $line) {
        $paragraphs .= '<a:p><a:pPr algn="'.$o['align'].'"><a:lnSpc><a:spcPct val="110000"/></a:lnSpc><a:spcAft><a:spcPts val="650"/></a:spcAft></a:pPr><a:r><a:rPr lang="en-US" sz="'.($o['size'] * 100).'" b="'.($o['bold'] ? 1 : 0).'"><a:solidFill><a:srgbClr val="'.$o['color'].'"/></a:solidFill><a:latin typeface="Aptos"/></a:rPr><a:t>'.xml($line).'</a:t></a:r><a:endParaRPr lang="en-US"/></a:p>';
    }
    return '<p:sp><p:nvSpPr><p:cNvPr id="'.$id.'" name="Shape '.$id.'"/><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="'.emu($o['x']).'" y="'.emu($o['y']).'"/><a:ext cx="'.emu($o['w']).'" cy="'.emu($o['h']).'"/></a:xfrm><a:prstGeom prst="'.$o['kind'].'"><a:avLst/></a:prstGeom>'.$fill.'<a:ln><a:noFill/></a:ln></p:spPr><p:txBody><a:bodyPr wrap="square" lIns="'.emu(.12).'" rIns="'.emu(.12).'" tIns="'.emu(.1).'" bIns="'.emu(.06).'" anchor="'.($o['align'] === 'ctr' ? 'ctr' : 't').'"><a:normAutofit/></a:bodyPr><a:lstStyle/>'.$paragraphs.'</p:txBody></p:sp>';
}
$ns = 'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"';
$group = '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr><p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>';
$relBase = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/';
function rels(array $items): string
{
    global $relBase;
    $result = '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    foreach ($items as $i => [$type, $target]) $result .= '<Relationship Id="rId'.($i + 1).'" Type="'.$relBase.$type.'" Target="'.xml($target).'"/>';
    return $result.'</Relationships>';
}
$zip = new ZipArchive;
$path = $out.'/PMS_Process_Presentation.pptx';
if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Cannot create presentation');
$types = '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>';
$entries = [
    'ppt/presentation.xml' => 'presentation.main', 'ppt/slideMasters/slideMaster1.xml' => 'slideMaster',
    'ppt/slideLayouts/slideLayout1.xml' => 'slideLayout', 'ppt/notesMasters/notesMaster1.xml' => 'notesMaster',
];
$slideIds = ''; $presentationRels = [['slideMaster', 'slideMasters/slideMaster1.xml'], ['notesMaster', 'notesMasters/notesMaster1.xml']];
$preview = '';
foreach ($slides as $i => $slide) {
    $n = $i + 1;
    $items = [
        ['x'=>0, 'y'=>0, 'w'=>13.3333, 'h'=>.08, 'text'=>'', 'fill'=>$teal, 'color'=>$ink, 'size'=>10, 'bold'=>false, 'align'=>'l', 'kind'=>'rect'],
        ['x'=>.62, 'y'=>.43, 'w'=>12.1, 'h'=>.7, 'text'=>$slide['title'], 'fill'=>'none', 'color'=>$navy, 'size'=>30, 'bold'=>true, 'align'=>'l', 'kind'=>'rect'],
        ['x'=>.65, 'y'=>1.2, 'w'=>12, 'h'=>.5, 'text'=>$slide['subtitle'], 'fill'=>'none', 'color'=>$muted, 'size'=>17, 'bold'=>false, 'align'=>'l', 'kind'=>'rect'],
        ...$slide['items'],
        ['x'=>.65, 'y'=>7.04, 'w'=>11.9, 'h'=>.25, 'text'=>'DENR-CAR  |  PERFORMANCE MONITORING SYSTEM                                               '.sprintf('%02d / %02d', $n, count($slides)), 'fill'=>'none', 'color'=>$muted, 'size'=>9, 'bold'=>false, 'align'=>'l', 'kind'=>'rect'],
    ];
    $shapeXml = ''; $svg = '<svg viewBox="0 0 1280 720" role="img" aria-label="'.xml($slide['title']).'"><rect width="1280" height="720" fill="#F2F6FA"/>';
    foreach ($items as $j => $item) {
        $shapeXml .= shapeXml($item, $j + 2);
        $x = $item['x'] * 96; $y = $item['y'] * 96; $w = $item['w'] * 96; $h = $item['h'] * 96;
        if (str_contains($item['kind'], 'Arrow')) {
            $points = $item['kind'] === 'downArrow' ? "0,0 $w,0 $w,".($h*.6).' '.($w/2).",$h 0,".($h*.6) : "0,0 ".($w*.6).",0 $w,".($h/2).' '.($w*.6).",$h 0,$h";
            $svg .= '<polygon points="'.$points.'" transform="translate('.$x.' '.$y.')'.($item['kind'] === 'leftArrow' ? ' rotate(180 '.($w/2).' '.($h/2).')' : '').'" fill="#'.$item['fill'].'"/>';
        } elseif ($item['fill'] !== 'none') $svg .= '<rect x="'.$x.'" y="'.$y.'" width="'.$w.'" height="'.$h.'" rx="'.($item['kind'] === 'roundRect' ? 12 : 0).'" fill="#'.$item['fill'].'"/>';
        if ($item['text'] !== '') {
            $font = $item['size'] * 96 / 72;
            $lines = [];
            $limit = max(8, (int)(($w - 26) / ($font * .49)));
            foreach (explode("\n", $item['text']) as $line) foreach (explode("\n", wordwrap($line, $limit)) as $wrapped) $lines[] = $wrapped;
            $center = $item['align'] === 'ctr';
            $tx = $center ? $x + $w / 2 : $x + 12;
            $ty = $center ? $y + ($h - count($lines) * $font * 1.15) / 2 + $font : $y + 10 + $font;
            $svg .= '<text x="'.$tx.'" y="'.$ty.'" font-family="Aptos, Segoe UI, sans-serif" font-size="'.$font.'" font-weight="'.($item['bold'] ? '700' : '400').'" text-anchor="'.($center ? 'middle' : 'start').'" fill="#'.$item['color'].'">';
            foreach ($lines as $k => $line) $svg .= '<tspan x="'.$tx.'" dy="'.($k ? $font * 1.2 : 0).'">'.xml($line).'</tspan>';
            $svg .= '</text>';
        }
    }
    $preview .= '<section'.($i ? ' hidden' : '').'>'.$svg.'</svg><details><summary>Speaker notes</summary><p>'.xml($slide['notes']).'</p></details></section>';
    $zip->addFromString("ppt/slides/slide$n.xml", '<p:sld '.$ns.'><p:cSld><p:bg><p:bgPr><a:solidFill><a:srgbClr val="F2F6FA"/></a:solidFill><a:effectLst/></p:bgPr></p:bg><p:spTree>'.$group.$shapeXml.'</p:spTree></p:cSld><p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr></p:sld>');
    $zip->addFromString("ppt/slides/_rels/slide$n.xml.rels", rels([['slideLayout', '../slideLayouts/slideLayout1.xml'], ['notesSlide', "../notesSlides/notesSlide$n.xml"]]));
    $notes = '<p:sp><p:nvSpPr><p:cNvPr id="2" name="Notes"/><p:cNvSpPr/><p:nvPr><p:ph type="body" idx="1"/></p:nvPr></p:nvSpPr><p:spPr/><p:txBody><a:bodyPr/><a:lstStyle/><a:p><a:r><a:t>'.xml($slide['notes']).'</a:t></a:r></a:p></p:txBody></p:sp>';
    $zip->addFromString("ppt/notesSlides/notesSlide$n.xml", '<p:notes '.$ns.'><p:cSld><p:spTree>'.$group.$notes.'</p:spTree></p:cSld><p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr></p:notes>');
    $zip->addFromString("ppt/notesSlides/_rels/notesSlide$n.xml.rels", rels([['notesMaster', '../notesMasters/notesMaster1.xml'], ['slide', "../slides/slide$n.xml"]]));
    $entries["ppt/slides/slide$n.xml"] = 'slide'; $entries["ppt/notesSlides/notesSlide$n.xml"] = 'notesSlide';
    $presentationRels[] = ['slide', "slides/slide$n.xml"];
    $slideIds .= '<p:sldId id="'.(255 + $n).'" r:id="rId'.($n + 2).'"/>';
}
$zip->addFromString('ppt/presentation.xml', '<p:presentation '.$ns.'><p:sldMasterIdLst><p:sldMasterId id="2147483648" r:id="rId1"/></p:sldMasterIdLst><p:notesMasterIdLst><p:notesMasterId r:id="rId2"/></p:notesMasterIdLst><p:sldIdLst>'.$slideIds.'</p:sldIdLst><p:sldSz cx="12192000" cy="6858000" type="screen16x9"/><p:notesSz cx="6858000" cy="9144000"/></p:presentation>');
$zip->addFromString('ppt/_rels/presentation.xml.rels', rels($presentationRels));
$colorMap = '<p:clrMap accent1="accent1" accent2="accent2" accent3="accent3" accent4="accent4" accent5="accent5" accent6="accent6" bg1="lt1" bg2="lt2" folHlink="folHlink" hlink="hlink" tx1="dk1" tx2="dk2"/>';
$zip->addFromString('ppt/slideMasters/slideMaster1.xml', '<p:sldMaster '.$ns.'><p:cSld><p:spTree>'.$group.'</p:spTree></p:cSld>'.$colorMap.'<p:sldLayoutIdLst><p:sldLayoutId id="2147483649" r:id="rId1"/></p:sldLayoutIdLst><p:txStyles><p:titleStyle/><p:bodyStyle/><p:otherStyle/></p:txStyles></p:sldMaster>');
$zip->addFromString('ppt/slideMasters/_rels/slideMaster1.xml.rels', rels([['slideLayout', '../slideLayouts/slideLayout1.xml'], ['theme', '../theme/theme1.xml']]));
$zip->addFromString('ppt/slideLayouts/slideLayout1.xml', '<p:sldLayout '.$ns.' type="blank" preserve="1"><p:cSld name="Blank"><p:spTree>'.$group.'</p:spTree></p:cSld><p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr></p:sldLayout>');
$zip->addFromString('ppt/slideLayouts/_rels/slideLayout1.xml.rels', rels([['slideMaster', '../slideMasters/slideMaster1.xml']]));
$zip->addFromString('ppt/notesMasters/notesMaster1.xml', '<p:notesMaster '.$ns.'><p:cSld><p:spTree>'.$group.'</p:spTree></p:cSld>'.$colorMap.'<p:notesStyle/></p:notesMaster>');
$zip->addFromString('ppt/notesMasters/_rels/notesMaster1.xml.rels', rels([['theme', '../theme/theme1.xml']]));
$colors = ['dk1'=>'213547','lt1'=>'FFFFFF','dk2'=>'17365D','lt2'=>'F2F6FA','accent1'=>'159B94','accent2'=>'2146B5','accent3'=>'EF4444','accent4'=>'F59E0B','accent5'=>'64748B','accent6'=>'B9E8E4','hlink'=>'2146B5','folHlink'=>'6941A5'];
$scheme = ''; foreach ($colors as $key => $value) $scheme .= "<a:$key><a:srgbClr val=\"$value\"/></a:$key>";
$fillStyle = '<a:solidFill><a:schemeClr val="phClr"/></a:solidFill>';
$lineStyle = '<a:ln w="9525">'.$fillStyle.'<a:prstDash val="solid"/></a:ln>';
$zip->addFromString('ppt/theme/theme1.xml', '<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="PMS"><a:themeElements><a:clrScheme name="PMS">'.$scheme.'</a:clrScheme><a:fontScheme name="Aptos"><a:majorFont><a:latin typeface="Aptos Display"/><a:ea typeface=""/><a:cs typeface=""/></a:majorFont><a:minorFont><a:latin typeface="Aptos"/><a:ea typeface=""/><a:cs typeface=""/></a:minorFont></a:fontScheme><a:fmtScheme name="PMS"><a:fillStyleLst>'.str_repeat($fillStyle, 3).'</a:fillStyleLst><a:lnStyleLst>'.str_repeat($lineStyle, 3).'</a:lnStyleLst><a:effectStyleLst>'.str_repeat('<a:effectStyle><a:effectLst/></a:effectStyle>', 3).'</a:effectStyleLst><a:bgFillStyleLst>'.str_repeat($fillStyle, 3).'</a:bgFillStyleLst></a:fmtScheme></a:themeElements></a:theme>');
foreach ($entries as $entry => $type) $types .= '<Override PartName="/'.$entry.'" ContentType="application/vnd.openxmlformats-officedocument.presentationml.'.$type.'+xml"/>';
$types .= '<Override PartName="/ppt/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/></Types>';
$zip->addFromString('[Content_Types].xml', $types);
$zip->addFromString('_rels/.rels', rels([['officeDocument', 'ppt/presentation.xml']]));
$zip->close();
file_put_contents($out.'/PMS_Process_Presentation.html', '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>PMS process presentation</title><style>body{margin:0;background:#14243a;color:#fff;font:16px "Segoe UI",sans-serif}main{max-width:1280px;margin:24px auto}svg{width:100%;display:block}nav{display:flex;justify-content:center;align-items:center;gap:20px;padding:16px}button{background:#159b94;border:0;border-radius:6px;padding:10px 22px;color:#fff;font-size:16px;cursor:pointer}details{padding:18px;background:#213547}p{line-height:1.6} @media print{body{background:white}section[hidden]{display:block}section{break-after:page}nav,details{display:none}main{margin:0}}</style><main>'.$preview.'</main><nav><button id="prev">Previous</button><span id="count"></span><button id="next">Next</button></nav><script>const slides=[...document.querySelectorAll("section")];let index=0;function show(n){index=Math.max(0,Math.min(slides.length-1,n));slides.forEach((s,i)=>s.hidden=i!==index);document.querySelector("#count").textContent=`${index+1} / ${slides.length}`;}document.querySelector("#prev").onclick=()=>show(index-1);document.querySelector("#next").onclick=()=>show(index+1);document.onkeydown=e=>{if(e.key==="ArrowRight"||e.key===" ")show(index+1);if(e.key==="ArrowLeft")show(index-1)};show(0);</script></html>');
// Verify every XML part and internal relationship target in the generated package.
$check = new ZipArchive; $check->open($path);
for ($i = 0; $i < $check->numFiles; $i++) {
    $name = $check->getNameIndex($i);
    $dom = new DOMDocument;
    if (!$dom->loadXML($check->getFromIndex($i))) throw new RuntimeException('Invalid XML: '.$name);
    if (str_ends_with($name, '.rels')) {
        $base = $name === '_rels/.rels' ? '' : dirname(dirname($name)).'/';
        foreach ($dom->getElementsByTagName('Relationship') as $r) {
            $parts = []; foreach (explode('/', $base.$r->getAttribute('Target')) as $p) { if ($p === '..') array_pop($parts); elseif ($p !== '.' && $p !== '') $parts[] = $p; }
            if ($check->locateName(implode('/', $parts)) === false) throw new RuntimeException('Missing relationship target in '.$name);
        }
    }
}
$check->close();
echo count($slides).' editable slides with speaker notes created and package verified.'.PHP_EOL;
echo $path.PHP_EOL;
