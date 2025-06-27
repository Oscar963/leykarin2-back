<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\PurchasePlan;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Language;

class ProjectsWordExport
{
    protected $purchasePlanId;

    public function __construct($purchasePlanId)
    {
        $this->purchasePlanId = $purchasePlanId;
    }

    public function export()
    {
        // Crear documento Word
        $phpWord = new PhpWord();
        
        // Configurar idioma
        $phpWord->getSettings()->setThemeFontLang(new Language(Language::ES_ES));
        
        // Propiedades del documento
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('Ilustre Municipalidad de Arica');
        $properties->setCompany('Ilustre Municipalidad de Arica');
        $properties->setTitle('Informe Plan de Compras');
        $properties->setDescription('Informe de proyectos del plan de compras');
        $properties->setCategory('Planes de Compra');
        $properties->setLastModifiedBy('Sistema');
        $properties->setCreated(time());
        $properties->setModified(time());
        $properties->setSubject('Plan de Compras');
        $properties->setKeywords('compras, proyectos, plan, presupuesto, municipalidad');

        // Crear sección con formato oficio chileno
        $section = $phpWord->addSection([
            'pageSizeW' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(21.59), // 21.59 cm de ancho
            'pageSizeH' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(33.02), // 33.02 cm de alto
            'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),   // 2.5 cm margen izquierdo
            'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),  // 2.5 cm margen derecho
            'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),    // 2.5 cm margen superior
            'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5), // 2.5 cm margen inferior
        ]);

        // Obtener información del plan de compra
        $purchasePlan = PurchasePlan::with(['direction'])
            ->find($this->purchasePlanId);



        // Encabezado con logo y texto del decreto
        $headerTable = $section->addTable();
        $headerTable->addRow();
        
        // Celda izquierda para logo (simulado con texto)
        $logoCell = $headerTable->addCell(2500);
        $logoCell->addText('ARICA', ['size' => 16, 'bold' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $logoCell->addText('MUNICIPALIDAD', ['size' => 10, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        // Celda derecha para texto del decreto
        $textCell = $headerTable->addCell(7500);
        $textCell->addText('INCORPORASE AL DECRETO ALCALDICIO', ['size' => 10, 'bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $decretoNum = 'N°XXXX/' . date('Y');
        $textCell->addText($decretoNum . ' DE FECHA ' . date('d') . ' DE ' . strtoupper($this->getMonthName(date('n'))) . ' DEL ' . date('Y') . ',', ['size' => 10, 'bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textCell->addText('QUE APRUEBA PLAN ANUAL DE', ['size' => 10, 'bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textCell->addText('COMPRAS DE LA IMA, DISAM Y DEMUCE', ['size' => 10, 'bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textCell->addText(date('Y') . ', LISTADO DE PROYECTOS Y', ['size' => 10, 'bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textCell->addText('SERVICIOS.', ['size' => 10, 'bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);

        $section->addTextBreak(2);

        // Decreto Alcaldicio - alineado a la derecha
        $section->addText('DECRETO ALCALDICIO ' . $decretoNum, [
            'size' => 12,
            'bold' => true
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END
        ]);
        $section->addText('ARICA, ' . date('d') . ' DE ' . strtoupper($this->getMonthName(date('n'))) . ' DE ' . date('Y'), [
            'size' => 11,
            'bold' => true
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END
        ]);

        $section->addTextBreak(1);

        // VISTOS
        $section->addText('VISTOS:', [
            'size' => 11, 
            'bold' => true
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH
        ]);
        $section->addText('Las facultades que me confiere la Ley N°18695 "Orgánica Constitucional de Municipalidades" y sus modificaciones; la Ley N°19.886, sobre Contratos Administrativos de Suministro y Prestación de Servicios; Reglamento N°181, de fecha 9 de enero del 2004, del Ministerio de Hacienda; Memorándum N°79, de fecha 12 de marzo del 2025, de SECPLAN; Reglamento Interno de la I. Municipalidad de Arica N°4988, de fecha 12 de marzo del 2025, de Administración Municipal;', [
            'size' => 10
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH,
            'spaceAfter' => 120
        ]);

        $section->addTextBreak(1);

        // CONSIDERANDO
        $section->addText('CONSIDERANDO:', [
            'size' => 11, 
            'bold' => true
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH
        ]);
        $section->addText('a) Que, mediante Decreto Alcaldicio N°1164 de fecha 31 de enero del 2025, que aprueba Plan Anual de Compras de la IMA, DISAM y DEMUCE ' . date('Y') . ';', [
            'size' => 10
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH,
            'spaceAfter' => 120
        ]);
        $section->addText('b) Que, por medio de memorándum N°79, de fecha 12 de marzo del 2025, de SECPLAN, que solicita incorporar listado de proyectos y servicios para Plan Anual de Compras ' . date('Y') . ';', [
            'size' => 10
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH,
            'spaceAfter' => 120
        ]);
        $section->addText('c) Que, es necesario modificar el Decreto Alcaldicio N°1164, de fecha 31 de marzo del 2025, de Administración Municipal, que autoriza decretar;', [
            'size' => 10
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH,
            'spaceAfter' => 120
        ]);
        $section->addText('d) Que, en virtud de lo expuesto y las facultades que me otorga la normativa jurídica;', [
            'size' => 10
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH,
            'spaceAfter' => 120
        ]);

        $section->addTextBreak(1);

        // DECRETO
        $section->addText('DECRETO:', ['size' => 11, 'bold' => true]);

        $section->addTextBreak(1);

        $section->addText('1. INCORPÓRASE al Decreto Alcaldicio N°1164/2025, listado de los siguientes proyectos y servicios en el Plan Anual de Compras ' . date('Y') . ', solicitado mediante Memorándum N°79, de fecha 12 de marzo del 2025, de Secretaría Comunal de Planificación, según detalle adjunto listado:', [
            'size' => 10
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH
        ]);

        $section->addTextBreak(1);

        // Estilo para la tabla
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 50,
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
        ];

        $phpWord->addTableStyle('ProjectsTable', $tableStyle);

        // Crear tabla
        $table = $section->addTable('ProjectsTable');

        // Estilo para encabezados
        $headerStyle = [
            'bold' => true,
            'size' => 11
        ];

        $headerCellStyle = [
            'valign' => 'center'
        ];

        // Agregar fila de encabezados
        $table->addRow(800);
        $table->addCell(1500, $headerCellStyle)->addText('UNIDAD REQUIRENTE', $headerStyle);
        $table->addCell(1500, $headerCellStyle)->addText('UNIDAD DE COMPRA', $headerStyle);
        $table->addCell(3000, $headerCellStyle)->addText('PROYECTO', $headerStyle);
        $table->addCell(4000, $headerCellStyle)->addText('DESCRIPCIÓN PROYECTO', $headerStyle);
        $table->addCell(2000, $headerCellStyle)->addText('MONTO', $headerStyle);

        // Obtener datos
        $projects = Project::where('purchase_plan_id', $this->purchasePlanId)
            ->with(['unitPurchasing', 'purchasePlan.direction', 'itemPurchases'])
            ->orderBy('id', 'desc')
            ->get();

        // Estilo para celdas de datos
        $cellStyle = [
            'size' => 11,
        ];

        $cellStyleCenter = [
            'size' => 11,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
        ];

        $cellStyleRight = [
            'size' => 11,
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END
        ];

        // Agregar filas de datos
        foreach ($projects as $index => $project) {
            $table->addRow();
            $table->addCell(1500)->addText($project->purchasePlan->direction->name ?? 'N/A', $cellStyle);
            $table->addCell(1500)->addText($project->unitPurchasing->name ?? 'N/A', $cellStyle);
            $table->addCell(3000)->addText($project->name, $cellStyle);
            $table->addCell(4000)->addText($project->description ?? 'Sin descripción', $cellStyle);
            $table->addCell(2000)->addText(number_format($project->getTotalAmount(), 0, '.', '.'), $cellStyleRight);
        }

        $section->addTextBreak(2);

        // Texto final del decreto
        $section->addText('Tendrán presente este Decreto Alcaldicio, Secretaría Municipal, la Dirección de Administración y Finanzas, Dirección de Control, Asesoría Jurídica y SECPLAN.', [
            'size' => 10
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH
        ]);

        $section->addTextBreak(1);

        // ANÓTESE, NOTIFÍQUESE Y ARCHÍVESE
        $section->addText('ANÓTESE, NOTIFÍQUESE Y ARCHÍVESE.', [
            'size' => 10,
            'bold' => true
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
        ]);

        $section->addTextBreak(1);

        // Firmas del documento
        $section->addText('(FDO.) MARCELO CANIPA ZEGARRA, ALCALDE DE ARICA (S) Y LORENA ZEPEDA FLORES, SECRETARIA MUNICIPAL(S).', [
            'size' => 10
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH
        ]);

        $section->addTextBreak(1);

        // Texto de transcripción
        $section->addText('Lo que transcribo a Ud., para su conocimiento y fines procedentes.', [
            'size' => 10
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH
        ]);

        $section->addTextBreak(4);

        // Espacio para sello y firma
        $section->addText('[ESPACIO PARA SELLO Y FIRMA]', [
            'size' => 10,
            'italic' => true
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
        ]);

        $section->addTextBreak(3);

        // Iniciales al pie
        $section->addText('MCZLZFIsd.-', [
            'size' => 9
        ]);
        
        $section->addText('Dirección de Administración y Finanzas, Dirección de Control, Asesoría Jurídica, Of. Propuesta, SECPLAN y Archivo.-', [
            'size' => 9
        ]);

        // Generar archivo
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        
        $filename = 'proyectos_plan_compra_' . $this->purchasePlanId . '_' . date('Y-m-d_H-i-s') . '.docx';
        $temp_file = tempnam(sys_get_temp_dir(), $filename);
        
        $objWriter->save($temp_file);
        
        return response()->download($temp_file, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    private function formatDate($date)
    {
        $months = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        $timestamp = strtotime($date);
        $day = date('j', $timestamp);
        $month = $months[date('n', $timestamp)];
        $year = date('Y', $timestamp);
        
        return "$month $day de $year";
    }

    private function getMonthName($monthNumber)
    {
        $months = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        return $months[$monthNumber] ?? 'enero';
    }
} 