<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Paper;
use PhpOffice\PhpWord\Element\Table;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\SimpleType\VerticalJc;
use PhpOffice\PhpWord\Shared\Converter; //sample09, sample13
use PhpOffice\PhpWord\Style\TablePosition; //sample09
use PhpOffice\PhpWord\SimpleType\NumberFormat; //sample06
use PhpOffice\PhpWord\ComplexType\FootnoteProperties; //sample06
use PhpOffice\PhpWord\Element\Section; //sample13

class TestPhpwordController extends Controller
{
    // SimpleText
    public function sample01()
    {
        $paper = new Paper();
        $paper->setSize('Folio');
        $languageEnGb = new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::EN_GB);
        $phpWord = new PhpWord();
        $phpWord->getSettings()->setThemeFontLang($languageEnGb);

        $fontStyleName = 'rStyle';
        $phpWord->addFontStyle($fontStyleName, ['bold' => true, 'italic' => true, 'size' => 16, 'allCaps' => true, 'doubleStrikethrough' => true]);

        $paragraphStyleName = 'pStyle';
        $phpWord->addParagraphStyle($paragraphStyleName, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100]);

        $phpWord->addTitleStyle(1, ['bold' => true], ['spaceAfter' => 240]);

        // New portrait section
        $section = $phpWord->addSection([
            'pageSizeW' => $paper->getWidth(),
            'pageSizeH' => $paper->getHeight(),
        ]);

        // Simple text
        $section->addTitle('Welcome to PhpWord', 1);
        $section->addText('Hello World!');

        // $pStyle = new Font();
        // $pStyle->setLang()
        $section->addText('Ce texte-ci est en français.', ['lang' => \PhpOffice\PhpWord\Style\Language::FR_BE]);

        // Two text break
        $section->addTextBreak(2);

        // Define styles
        $section->addText('I am styled by a font style definition.', $fontStyleName);
        $section->addText('I am styled by a paragraph style definition.', null, $paragraphStyleName);
        $section->addText('I am styled by both font and paragraph style.', $fontStyleName, $paragraphStyleName);

        $section->addTextBreak();

        // Inline font style
        $fontStyle['name'] = 'Times New Roman';
        $fontStyle['size'] = 20;

        $textrun = $section->addTextRun();
        $textrun->addText('I am inline styled ', $fontStyle);
        $textrun->addText('with ');
        $textrun->addText('color', ['color' => '996699']);
        $textrun->addText(', ');
        $textrun->addText('bold', ['bold' => true]);
        $textrun->addText(', ');
        $textrun->addText('italic', ['italic' => true]);
        $textrun->addText(', ');
        $textrun->addText('underline', ['underline' => 'dash']);
        $textrun->addText(', ');
        $textrun->addText('strikethrough', ['strikethrough' => true]);
        $textrun->addText(', ');
        $textrun->addText('doubleStrikethrough', ['doubleStrikethrough' => true]);
        $textrun->addText(', ');
        $textrun->addText('superScript', ['superScript' => true]);
        $textrun->addText(', ');
        $textrun->addText('subScript', ['subScript' => true]);
        $textrun->addText(', ');
        $textrun->addText('smallCaps', ['smallCaps' => true]);
        $textrun->addText(', ');
        $textrun->addText('allCaps', ['allCaps' => true]);
        $textrun->addText(', ');
        $textrun->addText('fgColor', ['fgColor' => 'yellow']);
        $textrun->addText(', ');
        $textrun->addText('scale', ['scale' => 200]);
        $textrun->addText(', ');
        $textrun->addText('spacing', ['spacing' => 120]);
        $textrun->addText(', ');
        $textrun->addText('kerning', ['kerning' => 10]);
        $textrun->addText('. ');

        // Link
        $section->addLink('https://github.com/PHPOffice/PHPWord', 'PHPWord on GitHub');
        $section->addTextBreak();



        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // TabStops
    public function sample02()
    {
        $phpWord = new PhpWord();

        $multipleTabsStyleName = 'multipleTab';
        $phpWord->addParagraphStyle(
            $multipleTabsStyleName,
            [
                'tabs' => [
                    new \PhpOffice\PhpWord\Style\Tab('left', 1550),
                    new \PhpOffice\PhpWord\Style\Tab('center', 3200),
                    new \PhpOffice\PhpWord\Style\Tab('right', 5300),
                ],
            ]
        );

        $rightTabStyleName = 'rightTab';
        $phpWord->addParagraphStyle($rightTabStyleName, ['tabs' => [new \PhpOffice\PhpWord\Style\Tab('right', 9090)]]);

        $leftTabStyleName = 'centerTab';
        $phpWord->addParagraphStyle($leftTabStyleName, ['tabs' => [new \PhpOffice\PhpWord\Style\Tab('center', 4680)]]);

        // New portrait section
        $section = $phpWord->addSection();

        // Add listitem elements
        $section->addText("Multiple Tabs:\tOne\tTwo\tThree", null, $multipleTabsStyleName);
        $section->addText("Left Aligned\tRight Aligned", null, $rightTabStyleName);
        $section->addText("\tCenter Aligned", null, $leftTabStyleName);



        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // Sections
    public function sample03()
    {
        $phpWord = new PhpWord();

        // New portrait section
        $section = $phpWord->addSection(['borderColor' => '00FF00', 'borderSize' => 12]);
        $section->addText('I am placed on a default section.');

        // New landscape section
        $section = $phpWord->addSection(['orientation' => 'landscape']);
        $section->addText('I am placed on a landscape section. Every page starting from this section will be landscape style.');
        $section->addPageBreak();
        $section->addPageBreak();

        // New portrait section
        $section = $phpWord->addSection(
            ['paperSize' => 'Folio', 'marginLeft' => 600, 'marginRight' => 600, 'marginTop' => 600, 'marginBottom' => 600]
        );
        $section->addText('This section uses other margins with folio papersize.');

        // The text of this section is vertically centered
        $section = $phpWord->addSection(
            ['vAlign' => VerticalJc::CENTER]
        );
        $section->addText('This section is vertically centered.');

        // New portrait section with Header & Footer
        $section = $phpWord->addSection(
            [
                'marginLeft' => 200,
                'marginRight' => 200,
                'marginTop' => 200,
                'marginBottom' => 200,
                'headerHeight' => 50,
                'footerHeight' => 50,
            ]
        );
        $section->addText('This section and we play with header/footer height.');
        $section->addHeader()->addText('Header');
        $section->addFooter()->addText('Footer');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // Textrun
    public function sample04()
    {
        $phpWord = new PhpWord();

        // Define styles
        $paragraphStyleName = 'pStyle';
        $phpWord->addParagraphStyle($paragraphStyleName, ['spacing' => 100]);

        $boldFontStyleName = 'BoldText';
        $phpWord->addFontStyle($boldFontStyleName, ['bold' => true]);

        $coloredFontStyleName = 'ColoredText';
        $phpWord->addFontStyle($coloredFontStyleName, ['color' => 'FF8080', 'bgColor' => 'FFFFCC']);

        $linkFontStyleName = 'NLink';
        $phpWord->addLinkStyle($linkFontStyleName, ['color' => '0000FF', 'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE]);

        // New portrait section
        $section = $phpWord->addSection();

        // Add text run
        $textrun = $section->addTextRun($paragraphStyleName);
        $textrun->addText('Each textrun can contain native text, link elements or an image.');
        $textrun->addText(' No break is placed after adding an element.', $boldFontStyleName);
        $textrun->addText(' Both ');
        $textrun->addText('superscript', ['superScript' => true]);
        $textrun->addText(' and ');
        $textrun->addText('subscript', ['subScript' => true]);
        $textrun->addText(' are also available.');
        $textrun->addText(' All elements are placed inside a paragraph with the optionally given paragraph style.', $coloredFontStyleName);
        $textrun->addText(' Sample Link: ');
        $textrun->addLink('https://github.com/PHPOffice/PHPWord', 'PHPWord on GitHub', $linkFontStyleName);
        $textrun->addText(' Sample Image: ');
        $textrun->addImage(public_path('images\bps-logo.png'),
        array(
            'width' => 18,
            'height' => 18,
        ));
        $textrun->addText(' Sample Object: ');
        $textrun->addObject(public_path('resources/_sheet.xls'));
        $textrun->addText(' Here is some more text. ');

        $textrun = $section->addTextRun();
        $textrun->addText('This text is not visible.', ['hidden' => true]);


        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // Multicolumn
    public function sample05()
    {
        $phpWord = new PhpWord();

        $filler = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. '
        . 'Nulla fermentum, tortor id adipiscing adipiscing, tortor turpis commodo. '
        . 'Donec vulputate iaculis metus, vel luctus dolor hendrerit ac. '
        . 'Suspendisse congue congue leo sed pellentesque.';

        // Normal
        $section = $phpWord->addSection();
        $section->addText("Normal paragraph. {$filler}");

        // Two columns
        $section = $phpWord->addSection(
            [
                'colsNum' => 2,
                'colsSpace' => 1440,
                'breakType' => 'continuous',
            ]
        );
        $section->addText("Two columns, one inch (1440 twips) spacing. {$filler}");

        // Normal
        $section = $phpWord->addSection(['breakType' => 'continuous']);
        $section->addText("Normal paragraph again. {$filler}");

        // Three columns
        $section = $phpWord->addSection(
            [
                'colsNum' => 3,
                'colsSpace' => 720,
                'breakType' => 'continuous',
            ]
        );
        $section->addText("Three columns, half inch (720 twips) spacing. {$filler}");

        // Normal
        $section = $phpWord->addSection(['breakType' => 'continuous']);
        $section->addText("Normal paragraph again. {$filler}");


        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // Footnote
    public function sample06()
    {
        $phpWord = new PhpWord();

        \PhpOffice\PhpWord\Settings::setCompatibility(false);

        // Define styles
        $paragraphStyleName = 'pStyle';
        $phpWord->addParagraphStyle($paragraphStyleName, ['spacing' => 100]);

        $boldFontStyleName = 'BoldText';
        $phpWord->addFontStyle($boldFontStyleName, ['bold' => true]);

        $coloredFontStyleName = 'ColoredText';
        $phpWord->addFontStyle($coloredFontStyleName, ['color' => 'FF8080', 'bgColor' => 'FFFFCC']);

        $linkFontStyleName = 'NLink';
        $phpWord->addLinkStyle($linkFontStyleName, ['color' => '0000FF', 'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE]);

        // New portrait section
        $section = $phpWord->addSection();

        // Add text elements
        $textrun = $section->addTextRun($paragraphStyleName);
        $textrun->addText('This is some lead text in a paragraph with a following footnote. ', $paragraphStyleName);

        $footnote = $textrun->addFootnote();
        $footnote->addText('Just like a textrun, a footnote can contain native texts. ');
        $footnote->addText('No break is placed after adding an element. ', $boldFontStyleName);
        $footnote->addText('All elements are placed inside a paragraph. ', $coloredFontStyleName);
        $footnote->addTextBreak();
        $footnote->addText('But you can insert a manual text break like above, ');
        $footnote->addText('links like ');
        $footnote->addLink('https://github.com/PHPOffice/PHPWord', 'PHPWord on GitHub', $linkFontStyleName);
        $footnote->addText(', image like ');
        $footnote->addImage(public_path('resources/_earth.jpg'), ['width' => 18, 'height' => 18]);
        $footnote->addText(', or object like ');
        $footnote->addObject(public_path('resources/_sheet.xls'));
        $footnote->addText('But you can only put footnote in section, not in header or footer.');

        $section->addText(
            'You can also create the footnote directly from the section making it wrap in a paragraph '
                . 'like the footnote below this paragraph. But is best used from within a textrun.'
        );
        $footnote = $section->addFootnote();
        $footnote->addText('The reference for this is wrapped in its own line');

        $footnoteProperties = new FootnoteProperties();
        $footnoteProperties->setNumFmt(NumberFormat::DECIMAL_ENCLOSED_CIRCLE);
        $section->setFootnoteProperties($footnoteProperties);


        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // TemplateCloneRow
    public function sample07()
    {
        $phpWord = new PhpWord();

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(public_path('resources/Sample_07_TemplateCloneRow.docx'));

        // Variables on different parts of document
        $action = app('request')->route()->getAction();
        $controller = class_basename($action['controller']);
        $templateProcessor->setValue('weekday', date('l'));            // On section/content
        $templateProcessor->setValue('time', date('H:i'));             // On footer
        $templateProcessor->setValue('serverName', $controller); // On header
        // Simple table
        $templateProcessor->cloneRow('rowValue', 10);

        $templateProcessor->setValue('rowValue#1', 'Sun');
        $templateProcessor->setValue('rowValue#2', 'Mercury');
        $templateProcessor->setValue('rowValue#3', 'Venus');
        $templateProcessor->setValue('rowValue#4', 'Earth');
        $templateProcessor->setValue('rowValue#5', 'Mars');
        $templateProcessor->setValue('rowValue#6', 'Jupiter');
        $templateProcessor->setValue('rowValue#7', 'Saturn');
        $templateProcessor->setValue('rowValue#8', 'Uranus');
        $templateProcessor->setValue('rowValue#9', 'Neptun');
        $templateProcessor->setValue('rowValue#10', 'Pluto');

        $templateProcessor->setValue('rowNumber#1', '1');
        $templateProcessor->setValue('rowNumber#2', '2');
        $templateProcessor->setValue('rowNumber#3', '3');
        $templateProcessor->setValue('rowNumber#4', '4');
        $templateProcessor->setValue('rowNumber#5', '5');
        $templateProcessor->setValue('rowNumber#6', '6');
        $templateProcessor->setValue('rowNumber#7', '7');
        $templateProcessor->setValue('rowNumber#8', '8');
        $templateProcessor->setValue('rowNumber#9', '9');
        $templateProcessor->setValue('rowNumber#10', '10');

        // Table with a spanned cell
        $values = [
            [
                'userId' => 1,
                'userFirstName' => 'James',
                'userName' => 'Taylor',
                'userPhone' => '+1 428 889 773',
            ],
            [
                'userId' => 2,
                'userFirstName' => 'Robert',
                'userName' => 'Bell',
                'userPhone' => '+1 428 889 774',
            ],
            [
                'userId' => 3,
                'userFirstName' => 'Michael',
                'userName' => 'Ray',
                'userPhone' => '+1 428 889 775',
            ]
        ];

        $templateProcessor->cloneRowAndSetValues('userId', $values);


        //this is equivalent to cloning and settings values with cloneRowAndSetValues
        // $templateProcessor->cloneRow('userId', 3);

        // $templateProcessor->setValue('userId#1', '1');
        // $templateProcessor->setValue('userFirstName#1', 'James');
        // $templateProcessor->setValue('userName#1', 'Taylor');
        // $templateProcessor->setValue('userPhone#1', '+1 428 889 773');

        // $templateProcessor->setValue('userId#2', '2');
        // $templateProcessor->setValue('userFirstName#2', 'Robert');
        // $templateProcessor->setValue('userName#2', 'Bell');
        // $templateProcessor->setValue('userPhone#2', '+1 428 889 774');

        // $templateProcessor->setValue('userId#3', '3');
        // $templateProcessor->setValue('userFirstName#3', 'Michael');
        // $templateProcessor->setValue('userName#3', 'Ray');
        // $templateProcessor->setValue('userPhone#3', '+1 428 889 775');

        $templateProcessor->saveAs(public_path('Sample_07_TemplateCloneRow.docx'));

        return response()->download(public_path('Sample_07_TemplateCloneRow.docx'))->deleteFileAfterSend(true);

    }
    // ParagraphPagination
    public function sample08()
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultParagraphStyle(
            [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH,
                'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(12),
                'spacing' => 120,
            ]
        );

        // New section
        $section = $phpWord->addSection();

        $section->addText(
            'Below are the samples on how to control your paragraph '
                . 'pagination. See "Line and Page Break" tab on paragraph properties '
                . 'window to see the attribute set by these controls.',
            ['bold' => true],
            ['space' => ['before' => 360, 'after' => 480]]
        );

        $section->addText(
            'Paragraph with widowControl = false (default: true). '
                . 'A "widow" is the last line of a paragraph printed by itself at the top '
                . 'of a page. An "orphan" is the first line of a paragraph printed by '
                . 'itself at the bottom of a page. Set this option to "false" if you want '
                . 'to disable this automatic control.',
            null,
            ['widowControl' => false, 'indentation' => ['left' => 240, 'right' => 120]]
        );

        $section->addText(
            'Paragraph with keepNext = true (default: false). '
                . '"Keep with next" is used to prevent Word from inserting automatic page '
                . 'breaks between paragraphs. Set this option to "true" if you do not want '
                . 'your paragraph to be separated with the next paragraph.',
            null,
            ['keepNext' => true, 'indentation' => ['firstLine' => 240]]
        );

        $section->addText(
            'Paragraph with keepLines = true (default: false). '
                . '"Keep lines together" will prevent Word from inserting an automatic page '
                . 'break within a paragraph. Set this option to "true" if you do not want '
                . 'all lines of your paragraph to be in the same page.',
            null,
            ['keepLines' => true, 'indentation' => ['left' => 240, 'hanging' => 240]]
        );

        $section->addText('Keep scrolling. More below.');

        $section->addText(
            'Paragraph with pageBreakBefore = true (default: false). '
                . 'Different with all other control above, "page break before" separates '
                . 'your paragraph into the next page. This option is most useful for '
                . 'heading styles.',
            null,
            ['pageBreakBefore' => true]
        );


        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // Table
    public function sample09()
    {
        $phpWord = new PhpWord();

        // New Word Document
        $section = $phpWord->addSection();
        $header = ['size' => 16, 'bold' => true];

        // 1. Basic table

        $rows = 10;
        $cols = 5;
        $section->addText('Basic table', $header);

        $table = $section->addTable();
        for ($r = 1; $r <= $rows; ++$r) {
            $table->addRow();
            for ($c = 1; $c <= $cols; ++$c) {
                $table->addCell(1750)->addText("Row {$r}, Cell {$c}");
            }
        }

        // 2. Advanced table

        $section->addTextBreak(1);
        $section->addText('Fancy table', $header);

        $fancyTableStyleName = 'Fancy Table';
        $fancyTableStyle = ['borderSize' => 6, 'borderColor' => '006699', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER, 'cellSpacing' => 50];
        $fancyTableFirstRowStyle = ['borderBottomSize' => 18, 'borderBottomColor' => '0000FF', 'bgColor' => '66BBFF'];
        $fancyTableCellStyle = ['valign' => 'center'];
        $fancyTableCellBtlrStyle = ['valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR];
        $fancyTableFontStyle = ['bold' => true];
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
        $table = $section->addTable($fancyTableStyleName);
        $table->addRow(900);
        $table->addCell(2000, $fancyTableCellStyle)->addText('Row 1', $fancyTableFontStyle);
        $table->addCell(2000, $fancyTableCellStyle)->addText('Row 2', $fancyTableFontStyle);
        $table->addCell(2000, $fancyTableCellStyle)->addText('Row 3', $fancyTableFontStyle);
        $table->addCell(2000, $fancyTableCellStyle)->addText('Row 4', $fancyTableFontStyle);
        $table->addCell(500, $fancyTableCellBtlrStyle)->addText('Row 5', $fancyTableFontStyle);
        for ($i = 1; $i <= 8; ++$i) {
            $table->addRow();
            $table->addCell(2000)->addText("Cell {$i}");
            $table->addCell(2000)->addText("Cell {$i}");
            $table->addCell(2000)->addText("Cell {$i}");
            $table->addCell(2000)->addText("Cell {$i}");
            $text = (0 == $i % 2) ? 'X' : '';
            $table->addCell(500)->addText($text);
        }

        /*
        *  3. colspan (gridSpan) and rowspan (vMerge)
        *  -------------------------
        *  |  A  |     B     |  C  |
        *  |-----|-----------|     |
        *  |        D        |     |
        *  ------|-----------|     |
        *  |  E  |  F  |  G  |     |
        *  -------------------------
        */

        $section->addPageBreak();
        $section->addText('Table with colspan and rowspan', $header);

        $fancyTableStyle = ['borderSize' => 6, 'borderColor' => '999999'];
        $cellRowSpan = ['vMerge' => 'restart', 'valign' => 'center', 'bgColor' => 'FFFF00'];
        $cellRowContinue = ['vMerge' => 'continue'];
        $cellColSpan = ['gridSpan' => 2, 'valign' => 'center'];
        $cellHCentered = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
        $cellVCentered = ['valign' => 'center'];

        $spanTableStyleName = 'Colspan Rowspan';
        $phpWord->addTableStyle($spanTableStyleName, $fancyTableStyle);
        $table = $section->addTable($spanTableStyleName);

        $row1 = $table->addRow();
        $row1->addCell(500)->addText('A');
        $row1->addCell(1000, ['gridSpan' => 2])->addText('B');
        $row1->addCell(500, ['vMerge' => 'restart'])->addText('C');

        $row2 = $table->addRow();
        $row2->addCell(1500, ['gridSpan' => 3])->addText('D');
        $row2->addCell(null, ['vMerge' => 'continue']);

        $row3 = $table->addRow();
        $row3->addCell(500)->addText('E');
        $row3->addCell(500)->addText('F');
        $row3->addCell(500)->addText('G');
        $row3->addCell(null, ['vMerge' => 'continue']);

        /*
        *  4. colspan (gridSpan) and rowspan (vMerge)
        *  ---------------------
        *  |     |   B    |  1 |
        *  |  A  |        |----|
        *  |     |        |  2 |
        *  |     |---|----|----|
        *  |     | C |  D |  3 |
        *  ---------------------
        * @see https://github.com/PHPOffice/PHPWord/issues/806
        */

        $section->addPageBreak();
        $section->addText('Table with colspan and rowspan', $header);

        $styleTable = ['borderSize' => 6, 'borderColor' => '999999'];
        $phpWord->addTableStyle('Colspan Rowspan', $styleTable);
        $table = $section->addTable('Colspan Rowspan');

        $row = $table->addRow();
        $row->addCell(1000, ['vMerge' => 'restart'])->addText('A');
        $row->addCell(1000, ['gridSpan' => 2, 'vMerge' => 'restart'])->addText('B');
        $row->addCell(1000)->addText('1');

        $row = $table->addRow();
        $row->addCell(1000, ['vMerge' => 'continue']);
        $row->addCell(1000, ['vMerge' => 'continue', 'gridSpan' => 2]);
        $row->addCell(1000)->addText('2');

        $row = $table->addRow();
        $row->addCell(1000, ['vMerge' => 'continue']);
        $row->addCell(1000)->addText('C');
        $row->addCell(1000)->addText('D');
        $row->addCell(1000)->addText('3');

        // 5. Nested table

        $section->addTextBreak(2);
        $section->addText('Nested table in a centered and 50% width table.', $header);

        $table = $section->addTable(['width' => 50 * 50, 'unit' => 'pct', 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER]);
        $cell = $table->addRow()->addCell();
        $cell->addText('This cell contains nested table.');
        $innerCell = $cell->addTable(['alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER])->addRow()->addCell();
        $innerCell->addText('Inside nested table');

        // 6. Table with floating position

        $section->addTextBreak(2);
        $section->addText('Table with floating positioning.', $header);

        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'position' => ['vertAnchor' => TablePosition::VANCHOR_TEXT, 'bottomFromText' => Converter::cmToTwip(1)]]);
        $cell = $table->addRow()->addCell();
        $cell->addText('This is a single cell.');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // HeaderFooter
    public function sample12()
    {
        $phpWord = new PhpWord();

        // New portrait section
        $section = $phpWord->addSection();

        // Add first page header
        $header = $section->addHeader();
        $header->firstPage();
        $table = $header->addTable();
        $table->addRow();
        $cell = $table->addCell(4500);
        $textrun = $cell->addTextRun();
        $textrun->addText('This is the header with ');
        $textrun->addLink('https://github.com/PHPOffice/PHPWord', 'PHPWord on GitHub');
        $table->addCell(4500)->addImage('resources/PhpWord.png', ['width' => 80, 'height' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);

        // Add header for all other pages
        $subsequent = $section->addHeader();
        $subsequent->addText('Subsequent pages in Section 1 will Have this!');
        $subsequent->addImage('resources/_mars.jpg', ['width' => 80, 'height' => 80]);

        // Add footer
        $footer = $section->addFooter();
        $footer->addPreserveText('Page {PAGE} of {NUMPAGES}.', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $footer->addLink('https://github.com/PHPOffice/PHPWord', 'PHPWord on GitHub');

        // Write some text
        $section->addTextBreak();
        $section->addText('Some text...');

        // Create a second page
        $section->addPageBreak();

        // Write some text
        $section->addTextBreak();
        $section->addText('Some text...');

        // Create a third page
        $section->addPageBreak();

        // Write some text
        $section->addTextBreak();
        $section->addText('Some text...');

        // New portrait section
        $section2 = $phpWord->addSection();

        $sec2Header = $section2->addHeader();
        $sec2Header->addText('All pages in Section 2 will Have this!');

        // Write some text
        $section2->addTextBreak();
        $section2->addText('Some text...');



        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }

    // for sample13
    private function printSeparator(Section $section): void
    {
        $section->addTextBreak();
        $lineStyle = ['weight' => 0.2, 'width' => 150, 'height' => 0, 'align' => 'center'];
        $section->addLine($lineStyle);
        $section->addTextBreak(2);
    }

    // Images
    function sample13()
    {
        $phpWord = new PhpWord();

        // Begin code
        $section = $phpWord->addSection();
        $section->addText('Local image without any styles:');
        $section->addImage('resources/_mars.jpg');

        $this->printSeparator($section);
        $section->addText('Local image with styles:');
        $section->addImage('resources/_earth.jpg', ['width' => 210, 'height' => 210, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // Remote image
        $this->printSeparator($section);
        $source = 'http://php.net/images/logos/php-med-trans-light.gif';
        $section->addText("Remote image from: {$source}");
        $section->addImage($source);

        // Image from string
        $this->printSeparator($section);
        $source = 'resources/_mars.jpg';
        $fileContent = file_get_contents($source);
        $section->addText('Image from string');
        $section->addImage($fileContent);

        //Wrapping style
        $this->printSeparator($section);
        $text = str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. ', 2);
        $wrappingStyles = ['inline', 'behind', 'infront', 'square', 'tight'];
        foreach ($wrappingStyles as $wrappingStyle) {
            $section->addText("Wrapping style {$wrappingStyle}");
            $section->addImage(
                'resources/_earth.jpg',
                [
                    'positioning' => 'relative',
                    'marginTop' => -1,
                    'marginLeft' => 1,
                    'width' => 80,
                    'height' => 80,
                    'wrappingStyle' => $wrappingStyle,
                    'wrapDistanceRight' => Converter::cmToPoint(1),
                    'wrapDistanceBottom' => Converter::cmToPoint(1),
                ]
            );
            $section->addText($text);
            $this->printSeparator($section);
        }

        //Absolute positioning
        $section->addText('Absolute positioning: see top right corner of page');
        $section->addImage(
            'resources/_mars.jpg',
            [
                'width' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(3),
                'height' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(3),
                'positioning' => \PhpOffice\PhpWord\Style\Image::POSITION_ABSOLUTE,
                'posHorizontal' => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_RIGHT,
                'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                'posVerticalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(15.5),
                'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(1.55),
            ]
        );

        //Relative positioning
        $this->printSeparator($section);
        $section->addText('Relative positioning: Horizontal position center relative to column,');
        $section->addText('Vertical position top relative to line');
        $section->addImage(
            'resources/_mars.jpg',
            [
                'width' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(3),
                'height' => \PhpOffice\PhpWord\Shared\Converter::cmToPixel(3),
                'positioning' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE,
                'posHorizontal' => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_CENTER,
                'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_COLUMN,
                'posVertical' => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_TOP,
                'posVerticalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_LINE,
            ]
        );

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // ListItem
    function sample14()
    {
        $phpWord = new PhpWord();

        // Define styles
        $fontStyleName = 'myOwnStyle';
        $phpWord->addFontStyle($fontStyleName, ['color' => 'FF0000']);

        $paragraphStyleName = 'P-Style';
        $phpWord->addParagraphStyle($paragraphStyleName, ['spaceAfter' => 95]);

        $multilevelNumberingStyleName = 'multilevel';
        $phpWord->addNumberingStyle(
            $multilevelNumberingStyleName,
            [
                'type' => 'multilevel',
                'levels' => [
                    ['format' => 'decimal', 'text' => '%1.', 'left' => 360, 'hanging' => 360, 'tabPos' => 360],
                    ['format' => 'upperLetter', 'text' => '%2.', 'left' => 720, 'hanging' => 360, 'tabPos' => 720],
                ],
            ]
        );

        $predefinedMultilevelStyle = ['listType' => \PhpOffice\PhpWord\Style\ListItem::TYPE_NUMBER_NESTED];

        // New section
        $section = $phpWord->addSection();

        // Lists
        $section->addText('Multilevel list.');
        $section->addListItem('List Item I', 0, null, $multilevelNumberingStyleName);
        $section->addListItem('List Item I.a', 1, null, $multilevelNumberingStyleName);
        $section->addListItem('List Item I.b', 1, null, $multilevelNumberingStyleName);
        $section->addListItem('List Item II', 0, null, $multilevelNumberingStyleName);
        $section->addListItem('List Item II.a', 1, null, $multilevelNumberingStyleName);
        $section->addListItem('List Item III', 0, null, $multilevelNumberingStyleName);
        $section->addTextBreak(2);

        $section->addText('Basic simple bulleted list.');
        $section->addListItem('List Item 1');
        $section->addListItem('List Item 2');
        $section->addListItem('List Item 3');
        $section->addTextBreak(2);

        $section->addText('Continue from multilevel list above.');
        $section->addListItem('List Item IV', 0, null, $multilevelNumberingStyleName);
        $section->addListItem('List Item IV.a', 1, null, $multilevelNumberingStyleName);
        $section->addTextBreak(2);

        $section->addText('Multilevel predefined list.');
        $section->addListItem('List Item 1', 0, $fontStyleName, $predefinedMultilevelStyle, $paragraphStyleName);
        $section->addListItem('List Item 2', 0, $fontStyleName, $predefinedMultilevelStyle, $paragraphStyleName);
        $section->addListItem('List Item 3', 1, $fontStyleName, $predefinedMultilevelStyle, $paragraphStyleName);
        $section->addListItem('List Item 4', 1, $fontStyleName, $predefinedMultilevelStyle, $paragraphStyleName);
        $section->addListItem('List Item 5', 2, $fontStyleName, $predefinedMultilevelStyle, $paragraphStyleName);
        $section->addListItem('List Item 6', 1, $fontStyleName, $predefinedMultilevelStyle, $paragraphStyleName);
        $section->addListItem('List Item 7', 0, $fontStyleName, $predefinedMultilevelStyle, $paragraphStyleName);
        $section->addTextBreak(2);

        $section->addText('List with inline formatting.');
        $listItemRun = $section->addListItemRun();
        $listItemRun->addText('List item 1');
        $listItemRun->addText(' in bold', ['bold' => true]);
        $listItemRun = $section->addListItemRun(1, $predefinedMultilevelStyle, $paragraphStyleName);
        $listItemRun->addText('List item 2');
        $listItemRun->addText(' in italic', ['italic' => true]);
        $footnote = $listItemRun->addFootnote();
        $footnote->addText('this is a footnote on a list item');
        $listItemRun = $section->addListItemRun();
        $listItemRun->addText('List item 3');
        $listItemRun->addText(' underlined', ['underline' => 'dash']);
        $section->addTextBreak(2);

        // Numbered heading
        $headingNumberingStyleName = 'headingNumbering';
        $phpWord->addNumberingStyle(
            $headingNumberingStyleName,
            ['type' => 'multilevel',
                'levels' => [
                    ['pStyle' => 'Heading1', 'format' => 'decimal', 'text' => '%1'],
                    ['pStyle' => 'Heading2', 'format' => 'decimal', 'text' => '%1.%2'],
                    ['pStyle' => 'Heading3', 'format' => 'decimal', 'text' => '%1.%2.%3'],
                ],
            ]
        );
        $phpWord->addTitleStyle(1, ['size' => 16], ['numStyle' => $headingNumberingStyleName, 'numLevel' => 0]);
        $phpWord->addTitleStyle(2, ['size' => 14], ['numStyle' => $headingNumberingStyleName, 'numLevel' => 1]);
        $phpWord->addTitleStyle(3, ['size' => 12], ['numStyle' => $headingNumberingStyleName, 'numLevel' => 2]);

        $section->addTitle('Heading 1', 1);
        $section->addTitle('Heading 2', 2);
        $section->addTitle('Heading 3', 3);

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // TitleTOC
    function sample17()
    {
        $phpWord = new PhpWord();

        $phpWord->getSettings()->setUpdateFields(true);

        // New section
        $section = $phpWord->addSection();

        // Define styles
        $fontStyle12 = ['spaceAfter' => 60, 'size' => 12];
        $fontStyle10 = ['size' => 10];
        $phpWord->addTitleStyle(null, ['size' => 22, 'bold' => true]);
        $phpWord->addTitleStyle(1, ['size' => 20, 'color' => '333333', 'bold' => true]);
        $phpWord->addTitleStyle(2, ['size' => 16, 'color' => '666666']);
        $phpWord->addTitleStyle(3, ['size' => 14, 'italic' => true]);
        $phpWord->addTitleStyle(4, ['size' => 12]);

        // Add text elements
        $section->addTitle('Table of contents 1', 0);
        $section->addTextBreak(2);

        // Add TOC #1
        $toc = $section->addTOC($fontStyle12);
        $section->addTextBreak(2);

        // Filler
        $section->addText('Text between TOC');
        $section->addTextBreak(2);

        // Add TOC #1
        $section->addText('Table of contents 2');
        $section->addTextBreak(2);
        $toc2 = $section->addTOC($fontStyle10);
        $toc2->setMinDepth(2);
        $toc2->setMaxDepth(3);

        // Add Titles
        $section->addPageBreak();
        $section->addTitle('Foo n Bar', 1);
        $section->addText('Some text...');
        $section->addTextBreak(2);

        $section->addTitle('I am a Subtitle of Title 1', 2);
        $section->addTextBreak(2);
        $section->addText('Some more text...');
        $section->addTextBreak(2);

        $section->addTitle('Another Title (Title 2)', 1);
        $section->addText('Some text...');
        $section->addPageBreak();
        $section->addTitle('I am Title 3', 1);
        $section->addText('And more text...');
        $section->addTextBreak(2);
        $section->addTitle('I am a Subtitle of Title 3', 2);
        $section->addText('Again and again, more text...');
        $section->addTitle('Subtitle 3.1.1', 3);
        $section->addText('Text');
        $section->addTitle('Subtitle 3.1.1.1', 4);
        $section->addText('Text');
        $section->addTitle('Subtitle 3.1.1.2', 4);
        $section->addText('Text');
        $section->addTitle('Subtitle 3.1.2', 3);
        $section->addText('Text');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // Watermark
    function sample18()
    {
        $phpWord = new PhpWord();

        // Begin code
        $section = $phpWord->addSection();
        $header = $section->addHeader();
        $header->addWatermark('resources/_earth.jpg', ['marginTop' => 200, 'marginLeft' => 55]);
        $section->addText('The header reference to the current section includes a watermark image.');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // TextBreak
    function sample19()
    {
        $phpWord = new PhpWord();

        // Define styles
        $fontStyle24 = ['size' => 24];

        $paragraphStyle24 = ['spacing' => 240, 'size' => 24];

        $fontStyleName = 'fontStyle';
        $phpWord->addFontStyle($fontStyleName, ['size' => 9]);

        $paragraphStyleName = 'paragraphStyle';
        $phpWord->addParagraphStyle($paragraphStyleName, ['spacing' => 480]);

        // New section
        $section = $phpWord->addSection();

        $section->addText('Text break with no style:');
        $section->addTextBreak();
        $section->addText('Text break with defined font style:');
        $section->addTextBreak(1, $fontStyleName);
        $section->addText('Text break with defined paragraph style:');
        $section->addTextBreak(1, null, $paragraphStyleName);
        $section->addText('Text break with inline font style:');
        $section->addTextBreak(1, $fontStyle24);
        $section->addText('Text break with inline paragraph style:');
        $section->addTextBreak(1, null, $paragraphStyle24);
        $section->addText('Done.');


        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // BGColor
    function sample20()
    {
        $phpWord = new PhpWord();

        // New section
        $section = $phpWord->addSection();

        $section->addText(
            'This is some text highlighted using fgColor (limited to 15 colors)',
            ['fgColor' => \PhpOffice\PhpWord\Style\Font::FGCOLOR_YELLOW]
        );
        $section->addText('This one uses bgColor and is using hex value (0xfbbb10)', ['bgColor' => 'fbbb10']);
        $section->addText('Compatible with font colors', ['color' => '0000ff', 'bgColor' => 'fbbb10']);

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // TableRowRules
    function sample21()
    {
        $phpWord = new PhpWord();


        // New section
        $section = $phpWord->addSection();

        $section->addText('By default, when you insert an image, it adds a textbreak after its content.');
        $section->addText('If we want a simple border around an image, we wrap the image inside a table->row->cell');
        $section->addText(
            'On the image with the red border, even if we set the row height to the height of the image, '
                . 'the textbreak is still there:'
        );

        $table1 = $section->addTable(['cellMargin' => 0, 'cellMarginRight' => 0, 'cellMarginBottom' => 0, 'cellMarginLeft' => 0]);
        $table1->addRow(3750);
        $cell1 = $table1->addCell(null, ['valign' => 'top', 'borderSize' => 30, 'borderColor' => 'ff0000']);
        $cell1->addImage('resources/_earth.jpg', ['width' => 250, 'height' => 250, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        $section->addTextBreak();
        $section->addText("But if we set the rowStyle 'exactHeight' to true, the real row height is used, removing the textbreak:");

        $table2 = $section->addTable(
            [
                'cellMargin' => 0,
                'cellMarginRight' => 0,
                'cellMarginBottom' => 0,
                'cellMarginLeft' => 0,
            ]
        );
        $table2->addRow(3750, ['exactHeight' => true]);
        $cell2 = $table2->addCell(null, ['valign' => 'top', 'borderSize' => 30, 'borderColor' => '00ff00']);
        $cell2->addImage('resources/_earth.jpg', ['width' => 250, 'height' => 250, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        $section->addTextBreak();
        $section->addText('In this example, image is 250px height. Rows are calculated in twips, and 1px = 15twips.');
        $section->addText('So: $' . "table2->addRow(3750, array('exactHeight'=>true));");

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // Checkbox
    function sample22()
    {
        $phpWord = new PhpWord();

        // New section
        // $checkedBox='<w:sym w:font="Wingdings" w:char="F0FE"/>';
        $section = $phpWord->addSection();

        $section->addText('Check box in section');
        $section->addCheckBox('chkBox1', 'Checkbox 1');
        $section->addText('Check box in table cell');
        $table = $section->addTable();
        $table->addRow();
        $cell = $table->addCell();
        $cell->addCheckBox('chkBox2', 'Checkbox 2');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // TemplateBlock
    function sample23()
    {
        $phpWord = new PhpWord();

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('resources/Sample_23_TemplateBlock.docx');

        // Will clone everything between ${tag} and ${/tag}, the number of times. By default, 1.
        $templateProcessor->cloneBlock('CLONEME', 3);

        // Everything between ${tag} and ${/tag}, will be deleted/erased.
        $templateProcessor->deleteBlock('DELETEME');

        $templateProcessor->saveAs(public_path('testPhpWord.docx'));

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    // ReadODText
    function sample24()
    {
        $phpWord = new PhpWord();


        // Read contents
        $name = "Sample_24_ReadODText";
        $source = "resources/{$name}.odt";

        $phpWord = \PhpOffice\PhpWord\IOFactory::load($source, 'ODText');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(public_path('testPhpWord.docx'));
        } catch (Exception $e) {
        }

        return response()->download(public_path('testPhpWord.docx'))->deleteFileAfterSend(true);

    }
    public function testSurtug()
    {
        // $phpWord = new PhpWord();

        // $properties = $phpWord->getDocInfo();
        // $properties->setCreator('Budi Yunior');
        // $properties->setCompany('BPS Karawang | SiMantor');

        $templateProcessor = new TemplateProcessor(public_path('templates/format_sk_umum_test.docx'));

        // Variables on different parts of document
        $templateProcessor->setValue('weekday', date('l'));            // On section/content
        $templateProcessor->setValue('time', date('H:i'));             // On footer
        $templateProcessor->setValue('serverName', realpath(__DIR__)); // On header
        $templateProcessor->setValue('nomor', 'B-425/32150/XII/2024');
        $templateProcessor->setValue('perihal', 'Pembentukan Tim Sosialisasi dan Pengawasan Tindak Pencucian Uang dan Judi Online Badan Pusat Statistik Kabupaten Karawang Tahun 2024');
        $templateProcessor->setValue('kepada', 'Tim');
        $templateProcessor->setValue('kegiatan', 'Sosialisasi dan Pengawasan Tindak Pencucian Uang dan Judi Online');
        $templateProcessor->setValue('ta', '2024');
        $templateProcessor->setValue('tgl_sk', '12 Oktober 2024');
        $templateProcessor->setValue('kepala_kantor', 'Robert Ronytua Pardosi');
        $templateProcessor->setValue('satu', 'Membentuk Tim Sosialisasi dan Pengawasan Tindak Pencucian Uang dan Judi Online');

        $uu=array(
            [
                'first' => 'Mengingat',
                'z' => ':',
                'i' => 1,
                'item' => 'Undang-Undang Nomor 16 Tahun 1997 tentang Statistik (Lembaran Negara Republik Indonesia Tahun 1997 Nomor 39 Tambahan Lembaran Negara Republik Indonesia Nomor 3683)',
            ],
            [
                'first' => '',
                'z' => '',
                'i' => 2,
                'item' => 'Peraturan Pemerintah Nomor 51 Tahun 1999 tentang penyelenggaraan Statistik (Lembaran Negara Republik Indonesia Tahun 1999 Nomor 96, Lembaran Negara Republik Indonesia Nomor 3854)',
            ],
            [
                'first' => '',
                'z' => '',
                'i' => 3,
                'item' => 'Peraturan Presiden Republik Indonesia Nomor 86 Tahun 2007 tentang Badan Pusat Statistik',
            ],
            [
                'first' => '',
                'z' => '',
                'i' => 4,
                'item' => 'Peraturan Kepala Badan Pusat Statistik Nomor 8 tahun 2020 tentang Organisasi dan Tata Kerja Badan Pusat Statistik Provinsi dan Badan Pusat Statistik Kabupaten/Kota',
            ],


        );

        $uu_tambahan = [
            [
                'item' => 'Undang-undang Pemerintahan No. 93 Tahun 2004 Tentang Pencucian Uang',
            ],
            [
                'item' => 'Undang-undang Pemerintahan No. 204 Tahun 2005 Tentang Peran Pegawai Pemerintah dalam Pencegahan Tindak Pencucian Uang',
            ],
            [
                'item' => 'Undang-undang Pemerintahan No. 65 Tahun 2018 Tentang Kerjasama Antar Negara dalam Pencegahan Tindak Pencucian Uang',
            ]
        ];

        $count = count($uu);
        foreach($uu_tambahan as $key => $value) {
            $value['first'] = '';
            $value['z'] = '';
            $value['i'] = $count+1+$key;
            array_push($uu, $value);
        }

        $templateProcessor->cloneRowAndSetValues('i', $uu);

        $tabelLampiran=array(
            [
                'n' => 'NO',
                'kol2' => 'NAMA',
                'kol3' => 'JABATAN DALAM DINAS',
                'kol4' => 'JABATAN DALAM TIM',
            ],
            [
                'n' => '(1)',
                'kol2' => '(2)',
                'kol3' => '(3)',
                'kol4' => '(4)',
            ],
            [
                'n' => '1.',
                'kol2' => 'Agus Praptono',
                'kol3' => 'Kepala Bagian Umum',
                'kol4' => 'KETUA',
            ],
            [
                'n' => '2.',
                'kol2' => 'Dony Jusfar',
                'kol3' => 'Arsiparis Muda',
                'kol4' => 'SEKRETARIS',
            ],
            [
                'n' => '3.',
                'kol2' => 'Diki Kurnia Sunarji',
                'kol3' => 'Arsiparis Pertama',
                'kol4' => 'ANGGOTA',
            ],
            [
                'n' => '4.',
                'kol2' => 'Vina Rosdiyana Saumi',
                'kol3' => 'Arsiparis Pertama',
                'kol4' => 'ANGGOTA',
            ],
            [
                'n' => '5.',
                'kol2' => 'Rini Septi Dewi Lestari',
                'kol3' => 'Arsiparis Terampil',
                'kol4' => 'ANGGOTA',
            ],
            [
                'n' => '6.',
                'kol2' => 'Budi Yunior',
                'kol3' => 'Prakom Ahli Muda',
                'kol4' => 'ANGGOTA',
            ],
        );

        $templateProcessor->cloneRowAndSetValues('n', $tabelLampiran);

        $templateProcessor->saveAs(public_path('testSurtug_TemplateCloneRow.docx'));

        return response()->download(public_path('testSurtug_TemplateCloneRow.docx'))->deleteFileAfterSend(true);

    }
}
