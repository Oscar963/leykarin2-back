<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetAllocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $allocations = [
            // 22-01: Alimentos y Bebidas
            ['description' => 'Alimentos y Bebidas Para Personas', 'code' => '22-01-001', 'cod_budget_allocation_type' => '82'],
            ['description' => 'Alimentos y Bebidas Para Animales', 'code' => '22-01-002', 'cod_budget_allocation_type' => '83'],

            // 22-02: Textiles y Vestuario
            ['description' => 'Textiles y Acabados Textiles', 'code' => '22-02-001', 'cod_budget_allocation_type' => '84'],
            ['description' => 'Vestuario, Accesorios y Prendas Diversas', 'code' => '22-02-002', 'cod_budget_allocation_type' => '85'],
            ['description' => 'Calzado', 'code' => '22-02-003', 'cod_budget_allocation_type' => '86'],

            // 22-03: Combustibles y Lubricantes
            ['description' => 'Combustibles y Lubricantes Para Vehículos', 'code' => '22-03-001', 'cod_budget_allocation_type' => '87'],
            ['description' => 'Combustibles y Lubricantes Para Maquinarias, Equipos de Producción, Tracción y Elevación', 'code' => '22-03-002', 'cod_budget_allocation_type' => '88'],
            ['description' => 'Combustibles y Lubricantes Para Calefacción', 'code' => '22-03-003', 'cod_budget_allocation_type' => '89'],
            ['description' => 'Combustibles y Lubricantes Para Otros', 'code' => '22-03-999', 'cod_budget_allocation_type' => '90'],

            // 22-04: Materiales diversos
            ['description' => 'Materiales de Oficina', 'code' => '22-04-001', 'cod_budget_allocation_type' => '91'],
            ['description' => 'Textos y Otros Materiales de Enseñanza', 'code' => '22-04-002', 'cod_budget_allocation_type' => '92'],
            ['description' => 'Productos Químicos', 'code' => '22-04-003', 'cod_budget_allocation_type' => '93'],
            ['description' => 'Productos Farmacéuticos', 'code' => '22-04-004', 'cod_budget_allocation_type' => '94'],
            ['description' => 'Materiales y Útiles Quirúrgicos', 'code' => '22-04-005', 'cod_budget_allocation_type' => '95'],
            ['description' => 'Fertilizantes, Insecticidas, Fungicidas y Otros', 'code' => '22-04-006', 'cod_budget_allocation_type' => '96'],
            ['description' => 'Materiales y Útiles de Aseo', 'code' => '22-04-007', 'cod_budget_allocation_type' => '97'],
            ['description' => 'Menaje para Oficina, Casino y Otros', 'code' => '22-04-008', 'cod_budget_allocation_type' => '98'],
            ['description' => 'Insumos, Repuestos y Accesorios Computacionales', 'code' => '22-04-009', 'cod_budget_allocation_type' => '99'],
            ['description' => 'Materiales para Mantenimiento y Reparaciones de Inmuebles', 'code' => '22-04-010', 'cod_budget_allocation_type' => '100'],
            ['description' => 'Repuestos y Accesorios para Mantenimiento y Reparaciones de Vehículos', 'code' => '22-04-011', 'cod_budget_allocation_type' => '101'],
            ['description' => 'Otros Materiales de uso o consumo, Repuestos y Útiles Diversos', 'code' => '22-04-012', 'cod_budget_allocation_type' => '102'],
            ['description' => 'Equipos Menores', 'code' => '22-04-013', 'cod_budget_allocation_type' => '103'],
            ['description' => 'Productos Elaborados de Cuero, Caucho y Plásticos', 'code' => '22-04-014', 'cod_budget_allocation_type' => '104'],
            ['description' => 'Productos Agropecuarios y Forestales', 'code' => '22-04-015', 'cod_budget_allocation_type' => '105'],
            ['description' => 'Materias Primas y Semielaboradas', 'code' => '22-04-016', 'cod_budget_allocation_type' => '106'],
            ['description' => 'Otros Materiales de Uso o Consumo', 'code' => '22-04-999', 'cod_budget_allocation_type' => '107'],

            // 22-05: Servicios Básicos
            ['description' => 'Electricidad', 'code' => '22-05-001', 'cod_budget_allocation_type' => '108'],
            ['description' => 'Agua', 'code' => '22-05-002', 'cod_budget_allocation_type' => '109'],
            ['description' => 'Gas', 'code' => '22-05-003', 'cod_budget_allocation_type' => '110'],
            ['description' => 'Correo', 'code' => '22-05-004', 'cod_budget_allocation_type' => '111'],
            ['description' => 'Telefonía Fija', 'code' => '22-05-005', 'cod_budget_allocation_type' => '112'],
            ['description' => 'Telefonía Celular', 'code' => '22-05-006', 'cod_budget_allocation_type' => '113'],
            ['description' => 'Acceso a Internet', 'code' => '22-05-007', 'cod_budget_allocation_type' => '114'],
            ['description' => 'Enlaces de Telecomunicaciones', 'code' => '22-05-008', 'cod_budget_allocation_type' => '115'],
            ['description' => 'Otros Servicios Básicos', 'code' => '22-05-999', 'cod_budget_allocation_type' => '116'],

            // Continúa con las demás categorías...
            // 22-06: Mantenimiento y Reparación
            ['description' => 'Mantenimiento y Reparación de Edificaciones', 'code' => '22-06-001', 'cod_budget_allocation_type' => '117'],
            ['description' => 'Mantenimiento y Reparación de Vehículos', 'code' => '22-06-002', 'cod_budget_allocation_type' => '118'],
            ['description' => 'Mantenimiento y Reparación de Mobiliarios y Otros', 'code' => '22-06-003', 'cod_budget_allocation_type' => '119'],
            ['description' => 'Mantenimiento y Reparación de Máquinas y Equipos de Oficina', 'code' => '22-06-004', 'cod_budget_allocation_type' => '120'],
            ['description' => 'Mantenimiento y Reparación de Maquinaria y Equipos de Producción', 'code' => '22-06-005', 'cod_budget_allocation_type' => '121'],
            ['description' => 'Mantenimiento y Reparación de Otras Maquinarias y Equipos', 'code' => '22-06-006', 'cod_budget_allocation_type' => '122'],
            ['description' => 'Mantenimiento y Reparación de Equipos Informáticos', 'code' => '22-06-007', 'cod_budget_allocation_type' => '123'],
            ['description' => 'Otros Mantenimiento y Reparaciones', 'code' => '22-06-999', 'cod_budget_allocation_type' => '124'],

            // 22-07: Publicidad y Difusión
            ['description' => 'Servicios de Publicidad', 'code' => '22-07-001', 'cod_budget_allocation_type' => '125'],
            ['description' => 'Servicios de Impresión', 'code' => '22-07-002', 'cod_budget_allocation_type' => '126'],
            ['description' => 'Servicios de Encuadernación y Empaste', 'code' => '22-07-003', 'cod_budget_allocation_type' => '127'],
            ['description' => 'Otros - Publicidad y Difusión', 'code' => '22-07-999', 'cod_budget_allocation_type' => '128'],

            // 22-08: Servicios Generales
            ['description' => 'Servicios de Aseo', 'code' => '22-08-001', 'cod_budget_allocation_type' => '129'],
            ['description' => 'Servicios de Vigilancia', 'code' => '22-08-002', 'cod_budget_allocation_type' => '130'],
            ['description' => 'Servicios de Mantención de Jardines', 'code' => '22-08-003', 'cod_budget_allocation_type' => '131'],
            ['description' => 'Servicios de Mantención de Alumbrado Público', 'code' => '22-08-004', 'cod_budget_allocation_type' => '132'],
            ['description' => 'Servicios de Mantención de Semáforos', 'code' => '22-08-005', 'cod_budget_allocation_type' => '133'],
            ['description' => 'Servicios de Mantención de Señalizaciones de Tránsito', 'code' => '22-08-006', 'cod_budget_allocation_type' => '134'],
            ['description' => 'Pasajes, Fletes y Bodegajes', 'code' => '22-08-007', 'cod_budget_allocation_type' => '135'],
            ['description' => 'Salas Cunas y/o Jardines Infantiles', 'code' => '22-08-008', 'cod_budget_allocation_type' => '136'],
            ['description' => 'Servicios de Pago y Cobranza', 'code' => '22-08-009', 'cod_budget_allocation_type' => '137'],
            ['description' => 'Servicios de Suscripción y Similares', 'code' => '22-08-010', 'cod_budget_allocation_type' => '138'],
            ['description' => 'Servicios de Producción y Desarrollo de Eventos', 'code' => '22-08-011', 'cod_budget_allocation_type' => '139'],
            ['description' => 'Otros Servicios Generales', 'code' => '22-08-999', 'cod_budget_allocation_type' => '140'],

            // 22-09: Arriendos
            ['description' => 'Arriendo de Terrenos', 'code' => '22-09-001', 'cod_budget_allocation_type' => '141'],
            ['description' => 'Arriendo de Edificios', 'code' => '22-09-002', 'cod_budget_allocation_type' => '142'],
            ['description' => 'Arriendo de Vehículos', 'code' => '22-09-003', 'cod_budget_allocation_type' => '143'],
            ['description' => 'Arriendo de Mobiliario y Otros', 'code' => '22-09-004', 'cod_budget_allocation_type' => '144'],
            ['description' => 'Arriendo de Máquinas y Equipos', 'code' => '22-09-005', 'cod_budget_allocation_type' => '145'],
            ['description' => 'Arriendo de Equipos Informáticos', 'code' => '22-09-006', 'cod_budget_allocation_type' => '146'],
            ['description' => 'Otros Arriendos', 'code' => '22-09-999', 'cod_budget_allocation_type' => '147'],

            // 22-10: Servicios Financieros y de Seguros
            ['description' => 'Gastos Financieros por Compra y Venta de Títulos y Valores', 'code' => '22-10-001', 'cod_budget_allocation_type' => '148'],
            ['description' => 'Primas y Gastos de Seguros', 'code' => '22-10-002', 'cod_budget_allocation_type' => '149'],
            ['description' => 'Servicios de Giros y Remesas', 'code' => '22-10-003', 'cod_budget_allocation_type' => '150'],
            ['description' => 'Gastos Bancarios', 'code' => '22-10-004', 'cod_budget_allocation_type' => '151'],
            ['description' => 'Otros Servicios Financieros y de Seguros', 'code' => '22-10-999', 'cod_budget_allocation_type' => '152'],

            // 22-11: Servicios Técnicos y Profesionales
            ['description' => 'Estudios e Investigaciones', 'code' => '22-11-001', 'cod_budget_allocation_type' => '153'],
            ['description' => 'Cursos de Capacitación', 'code' => '22-11-002', 'cod_budget_allocation_type' => '154'],
            ['description' => 'Servicios Informáticos', 'code' => '22-11-003', 'cod_budget_allocation_type' => '155'],
            ['description' => 'Otros Servicios Técnicos y Profesionales', 'code' => '22-11-999', 'cod_budget_allocation_type' => '156'],

            // 22-12: Otros Gastos
            ['description' => 'Gastos Reservados', 'code' => '22-12-001', 'cod_budget_allocation_type' => '157'],
            ['description' => 'Gastos Menores', 'code' => '22-12-002', 'cod_budget_allocation_type' => '158'],
            ['description' => 'Gastos de Representación, Protocolo y Ceremonial', 'code' => '22-12-003', 'cod_budget_allocation_type' => '159'],
            ['description' => 'Intereses, Multas y Recargos', 'code' => '22-12-004', 'cod_budget_allocation_type' => '160'],
            ['description' => 'Derechos y Tasas', 'code' => '22-12-005', 'cod_budget_allocation_type' => '161'],
            ['description' => 'Contribuciones', 'code' => '22-12-006', 'cod_budget_allocation_type' => '162'],
            ['description' => 'Otros Gastos en Bienes y Servicios de Consumo', 'code' => '22-12-999', 'cod_budget_allocation_type' => '163'],

            // 24: Transferencias Corrientes
            ['description' => 'Transferencias Corrientes al Sector Privado', 'code' => '24-01', 'cod_budget_allocation_type' => '164'],
            ['description' => 'Transferencias Corrientes al Gobierno Central', 'code' => '24-02', 'cod_budget_allocation_type' => '165'],
            ['description' => 'Transferencias Corrientes a otras Entidades Públicas', 'code' => '24-03', 'cod_budget_allocation_type' => '166'],
            ['description' => 'Transferencias Corrientes a Empresas Públicas no Financieras', 'code' => '24-04', 'cod_budget_allocation_type' => '167'],
            ['description' => 'Transferencias Corrientes a Empresas Públicas Financieras', 'code' => '24-05', 'cod_budget_allocation_type' => '168'],
            ['description' => 'Transferencias Corrientes a Gobiernos Extranjeros', 'code' => '24-06', 'cod_budget_allocation_type' => '169'],
            ['description' => 'Transferencias Corrientes a Organismos Internacionales', 'code' => '24-07', 'cod_budget_allocation_type' => '170'],

            // 29: Adquisición de Activos no Financieros
            ['description' => 'Adquisición de Terrenos', 'code' => '29-01', 'cod_budget_allocation_type' => '171'],
            ['description' => 'Adquisición de Edificios', 'code' => '29-02', 'cod_budget_allocation_type' => '172'],
            ['description' => 'Adquisición de Vehículos', 'code' => '29-03', 'cod_budget_allocation_type' => '173'],
            ['description' => 'Adquisición de Mobiliario y Otros', 'code' => '29-04', 'cod_budget_allocation_type' => '174'],
            ['description' => 'Adquisición de Máquinas y Equipos de Oficina', 'code' => '29-05-001', 'cod_budget_allocation_type' => '175'],
            ['description' => 'Adquisición de Maquinarias y Equipos para la Producción', 'code' => '29-05-002', 'cod_budget_allocation_type' => '176'],
            ['description' => 'Otras Adquisiciones de Maquinas y Equipos', 'code' => '29-05-999', 'cod_budget_allocation_type' => '177'],
            ['description' => 'Equipos Computacionales y Periféricos', 'code' => '29-06-001', 'cod_budget_allocation_type' => '178'],
            ['description' => 'Equipos de Comunicaciones para Redes Informáticas', 'code' => '29-06-002', 'cod_budget_allocation_type' => '179'],
            ['description' => 'Programas Computacionales', 'code' => '29-07-001', 'cod_budget_allocation_type' => '180'],
            ['description' => 'Sistemas de Información', 'code' => '29-07-002', 'cod_budget_allocation_type' => '181'],
            ['description' => 'Otros Activos no Financieros', 'code' => '29-99', 'cod_budget_allocation_type' => '182'],

            // 31: Gastos de Inversión
            ['description' => 'Gastos Administrativos de Estudios Básicos', 'code' => '31-01-001', 'cod_budget_allocation_type' => '183'],
            ['description' => 'Consultorías - Estudios Básicos', 'code' => '31-01-002', 'cod_budget_allocation_type' => '184'],
            ['description' => 'Gastos Administrativos - de Proyectos', 'code' => '31-02-001', 'cod_budget_allocation_type' => '185'],
            ['description' => 'Consultorías - de Proyectos', 'code' => '31-02-002', 'cod_budget_allocation_type' => '186'],
            ['description' => 'Terrenos - de Proyectos', 'code' => '31-02-003', 'cod_budget_allocation_type' => '187'],
            ['description' => 'Obras Civiles - de Proyectos', 'code' => '31-02-004', 'cod_budget_allocation_type' => '188'],
            ['description' => 'Equipamiento - de Proyectos', 'code' => '31-02-005', 'cod_budget_allocation_type' => '189'],
            ['description' => 'Equipos - de Proyectos', 'code' => '31-02-006', 'cod_budget_allocation_type' => '190'],
            ['description' => 'Vehículos - de Proyectos', 'code' => '31-02-007', 'cod_budget_allocation_type' => '191'],
            ['description' => 'Otros Gastos - de Proyectos', 'code' => '31-02-999', 'cod_budget_allocation_type' => '192'],
            ['description' => 'Gastos Administrativos - de Programas de Inversión', 'code' => '31-03-001', 'cod_budget_allocation_type' => '193'],
            ['description' => 'Consultorías - de Programas de Inversión', 'code' => '31-03-002', 'cod_budget_allocation_type' => '194'],
            ['description' => 'Contratación del Programa de Inversión', 'code' => '31-03-003', 'cod_budget_allocation_type' => '195'],
            ['description' => 'Otros', 'code' => '31-03-999', 'cod_budget_allocation_type' => '196'],
        ];

        // Insertar en lotes para mejor performance
        $chunks = array_chunk($allocations, 50);
        foreach ($chunks as $chunk) {
            DB::table('budget_allocations')->insert($chunk);
        }
    }
}
