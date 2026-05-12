<?php

namespace Database\Seeders;

use App\Src\Infrastructure\Persistence\Models\Activity;
use App\Src\Infrastructure\Persistence\Models\Contract;
use App\Src\Infrastructure\Persistence\Models\ContractAmendment;
use App\Src\Infrastructure\Persistence\Models\ContractService;
use App\Src\Infrastructure\Persistence\Models\Invoice;
use App\Src\Infrastructure\Persistence\Models\Microservice;
use App\Src\Infrastructure\Persistence\Models\Plan;
use App\Src\Infrastructure\Persistence\Models\PlanService;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use App\Src\Infrastructure\Persistence\Models\Quotation;
use App\Src\Infrastructure\Persistence\Models\QuotationItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AccountingSeedDataSeeder extends Seeder
{
    private const MONTHLY = 'recurring';
    private const ONCE = 'one_time';

    public function run(): void
    {
        // 1. Eliminar datos existentes (orden inverso a FK)
        Schema::disableForeignKeyConstraints();

        Activity::query()->delete();
        Invoice::query()->delete();
        ContractAmendment::query()->delete();
        ContractService::query()->delete();
        Contract::query()->delete();
        QuotationItem::query()->delete();
        Quotation::query()->delete();
        Prospect::query()->delete();
        PlanService::query()->delete();
        Plan::query()->delete();
        Activity::query()->delete();
        Microservice::query()->delete();

        Schema::enableForeignKeyConstraints();

        $this->command->info('Datos anteriores eliminados correctamente.');

        // 2. Crear microservicios contables
        $msNomina = Microservice::create([
            'name' => 'Gestión de Nómina',
            'description' => 'Procesamiento completo de nómina electrónica, liquidación de prestaciones sociales y generación de certificados laborales.',
            'base_cost' => 250000,
            'type' => self::MONTHLY,
        ]);

        $msFactElect = Microservice::create([
            'name' => 'Facturación Electrónica',
            'description' => 'Emisión, envío y validación de facturas electrónicas ante la DIAN con soporte de contingencia.',
            'base_cost' => 180000,
            'type' => self::MONTHLY,
        ]);

        $msContabGen = Microservice::create([
            'name' => 'Contabilidad General',
            'description' => 'Registro de asientos contables, elaboración de estados financieros y cierres contables mensuales.',
            'base_cost' => 320000,
            'type' => self::MONTHLY,
        ]);

        $msImpuestos = Microservice::create([
            'name' => 'Gestión de Impuestos',
            'description' => 'Liquidación y presentación de declaraciones de renta, IVA, ICA y retenciones ante las autoridades tributarias.',
            'base_cost' => 200000,
            'type' => self::MONTHLY,
        ]);

        $msAuditoria = Microservice::create([
            'name' => 'Auditoría Interna',
            'description' => 'Revisión de procesos contables y financieros, detección de riesgos y recomendaciones de control interno.',
            'base_cost' => 400000,
            'type' => self::ONCE,
        ]);

        $msConciliacion = Microservice::create([
            'name' => 'Conciliación Bancaria',
            'description' => 'Conciliación mensual de extractos bancarios contra registros contables con detección de diferencias.',
            'base_cost' => 120000,
            'type' => self::MONTHLY,
        ]);

        $msCartera = Microservice::create([
            'name' => 'Gestión de Cartera',
            'description' => 'Administración de cuentas por cobrar, seguimiento de vencimientos y generación de reports de morosidad.',
            'base_cost' => 150000,
            'type' => self::MONTHLY,
        ]);

        $msPresupuesto = Microservice::create([
            'name' => 'Elaboración de Presupuestos',
            'description' => 'Diseño y seguimiento de presupuestos anuales con proyecciones financieras y análisis de variaciones.',
            'base_cost' => 280000,
            'type' => self::ONCE,
        ]);

        $this->command->info('8 microservicios contables creados.');

        // 3. Crear actividades para cada microservicio
        // Las primeras 1-2 actividades de cada microservicio son esenciales (no se pueden desactivar)
        // Nómina
        Activity::create(['microservice_id' => $msNomina->id, 'name' => 'Liquidación de nómina mensual', 'is_essential' => true]);
        Activity::create(['microservice_id' => $msNomina->id, 'name' => 'Cálculo de prestaciones sociales', 'is_essential' => true]);
        Activity::create(['microservice_id' => $msNomina->id, 'name' => 'Generación de certificados laborales']);
        Activity::create(['microservice_id' => $msNomina->id, 'name' => 'Reporte de nómina a la DIAN']);
        Activity::create(['microservice_id' => $msNomina->id, 'name' => 'Soporte a trabajadores']);

        // Facturación Electrónica
        Activity::create(['microservice_id' => $msFactElect->id, 'name' => 'Emisión de facturas electrónicas', 'is_essential' => true]);
        Activity::create(['microservice_id' => $msFactElect->id, 'name' => 'Validación ante la DIAN', 'is_essential' => true]);
        Activity::create(['microservice_id' => $msFactElect->id, 'name' => 'Envío de facturas a clientes']);
        Activity::create(['microservice_id' => $msFactElect->id, 'name' => 'Gestión de contingencia']);
        Activity::create(['microservice_id' => $msFactElect->id, 'name' => 'Reporte de facturación mensual']);

        // Contabilidad General
        Activity::create(['microservice_id' => $msContabGen->id, 'name' => 'Registro de asientos contables', 'is_essential' => true]);
        Activity::create(['microservice_id' => $msContabGen->id, 'name' => 'Elaboración de balance general', 'is_essential' => true]);
        Activity::create(['microservice_id' => $msContabGen->id, 'name' => 'Elaboración de estado de resultados']);
        Activity::create(['microservice_id' => $msContabGen->id, 'name' => 'Cierre contable mensual']);
        Activity::create(['microservice_id' => $msContabGen->id, 'name' => 'Libros oficiales (diario y mayor)']);

        // Gestión de Impuestos
        Activity::create(['microservice_id' => $msImpuestos->id, 'name' => 'Liquidación de IVA mensual', 'is_essential' => true]);
        Activity::create(['microservice_id' => $msImpuestos->id, 'name' => 'Declaración de renta anual', 'is_essential' => true]);
        Activity::create(['microservice_id' => $msImpuestos->id, 'name' => 'Declaración de ICA']);
        Activity::create(['microservice_id' => $msImpuestos->id, 'name' => 'Cálculo de retenciones']);
        Activity::create(['microservice_id' => $msImpuestos->id, 'name' => 'Soporte a requerimientos tributarios']);

        // Auditoría Interna
        Activity::create(['microservice_id' => $msAuditoria->id, 'name' => 'Revisión de procesos contables', 'is_essential' => true]);
        Activity::create(['microservice_id' => $msAuditoria->id, 'name' => 'Evaluación de control interno', 'is_essential' => true]);
        Activity::create(['microservice_id' => $msAuditoria->id, 'name' => 'Detección de riesgos financieros']);
        Activity::create(['microservice_id' => $msAuditoria->id, 'name' => 'Informe de auditoría']);
        Activity::create(['microservice_id' => $msAuditoria->id, 'name' => 'Recomendaciones de mejora']);

        // Conciliación Bancaria
        Activity::create(['microservice_id' => $msConciliacion->id, 'name' => 'Conciliación mensual bancaria', 'is_essential' => true]);
        Activity::create(['microservice_id' => $msConciliacion->id, 'name' => 'Detección de diferencias']);
        Activity::create(['microservice_id' => $msConciliacion->id, 'name' => 'Ajustes contables por conciliación']);
        Activity::create(['microservice_id' => $msConciliacion->id, 'name' => 'Reporte de conciliación']);

        // Gestión de Cartera
        Activity::create(['microservice_id' => $msCartera->id, 'name' => 'Seguimiento de cuentas por cobrar', 'is_essential' => true]);
        Activity::create(['microservice_id' => $msCartera->id, 'name' => 'Reporte de morosidad']);
        Activity::create(['microservice_id' => $msCartera->id, 'name' => 'Gestión de cobranza']);
        Activity::create(['microservice_id' => $msCartera->id, 'name' => 'Análisis de cartera por edades']);

        // Presupuestos
        Activity::create(['microservice_id' => $msPresupuesto->id, 'name' => 'Diseño de presupuesto anual', 'is_essential' => true]);
        Activity::create(['microservice_id' => $msPresupuesto->id, 'name' => 'Proyecciones financieras']);
        Activity::create(['microservice_id' => $msPresupuesto->id, 'name' => 'Seguimiento de ejecución presupuestal']);
        Activity::create(['microservice_id' => $msPresupuesto->id, 'name' => 'Análisis de variaciones']);

        $this->command->info('Actividades creadas para cada microservicio.');

        // 4. Crear planes contables
        $planBasico = Plan::create([
            'name' => 'Plan Contable Básico',
            'description' => 'Servicios esenciales de contabilidad para pequeñas empresas: facturación electrónica, conciliación bancaria y gestión de cartera.',
            'is_active' => true,
        ]);
        $planBasico->services()->createMany([
            ['microservice_id' => $msFactElect->id, 'custom_price' => 150000],
            ['microservice_id' => $msConciliacion->id],
            ['microservice_id' => $msCartera->id],
        ]);

        $planEmpresarial = Plan::create([
            'name' => 'Plan Contable Empresarial',
            'description' => 'Solución completa de contabilidad para medianas empresas: nómina, contabilidad general, impuestos y facturación electrónica.',
            'is_active' => true,
        ]);
        $planEmpresarial->services()->createMany([
            ['microservice_id' => $msNomina->id],
            ['microservice_id' => $msFactElect->id],
            ['microservice_id' => $msContabGen->id],
            ['microservice_id' => $msImpuestos->id],
            ['microservice_id' => $msConciliacion->id],
        ]);

        $planCorporativo = Plan::create([
            'name' => 'Plan Contable Corporativo',
            'description' => 'Gestión financiera integral para grandes empresas: todos los servicios contables más auditoría interna y presupuestos.',
            'is_active' => true,
        ]);
        $planCorporativo->services()->createMany([
            ['microservice_id' => $msNomina->id, 'custom_price' => 220000],
            ['microservice_id' => $msFactElect->id],
            ['microservice_id' => $msContabGen->id, 'custom_price' => 280000],
            ['microservice_id' => $msImpuestos->id, 'custom_price' => 180000],
            ['microservice_id' => $msAuditoria->id, ],
            ['microservice_id' => $msConciliacion->id],
            ['microservice_id' => $msCartera->id],
            ['microservice_id' => $msPresupuesto->id],
        ]);

        $planTributario = Plan::create([
            'name' => 'Plan Tributario',
            'description' => 'Paquete especializado en impuestos y cumplimiento tributario: gestión de impuestos, facturación electrónica y conciliación.',
            'is_active' => true,
        ]);
        $planTributario->services()->createMany([
            ['microservice_id' => $msImpuestos->id],
            ['microservice_id' => $msConciliacion->id],
            ['microservice_id' => $msCartera->id],
        ]);

        $planEmpresarial = Plan::create([
            'name' => 'Plan Contable Empresarial',
            'description' => 'Solución completa de contabilidad para medianas empresas: nómina, contabilidad general, impuestos y facturación electrónica.',
            'is_active' => true,
        ]);
        $planEmpresarial->services()->createMany([
            ['microservice_id' => $msNomina->id],
            ['microservice_id' => $msFactElect->id],
            ['microservice_id' => $msContabGen->id],
            ['microservice_id' => $msImpuestos->id],
            ['microservice_id' => $msConciliacion->id],
        ]);

        $planCorporativo = Plan::create([
            'name' => 'Plan Contable Corporativo',
            'description' => 'Gestión financiera integral para grandes empresas: todos los servicios contables más auditoría interna y presupuestos.',
            'is_active' => true,
        ]);
        $planCorporativo->services()->createMany([
            ['microservice_id' => $msNomina->id, 'custom_price' => 220000],
            ['microservice_id' => $msFactElect->id],
            ['microservice_id' => $msContabGen->id, 'custom_price' => 280000],
            ['microservice_id' => $msImpuestos->id, 'custom_price' => 180000],
            ['microservice_id' => $msAuditoria->id],
            ['microservice_id' => $msConciliacion->id],
            ['microservice_id' => $msCartera->id],
            ['microservice_id' => $msPresupuesto->id],
        ]);

        $planTributario = Plan::create([
            'name' => 'Plan Tributario',
            'description' => 'Paquete especializado en impuestos y cumplimiento tributario: gestión de impuestos, facturación electrónica y conciliación.',
            'is_active' => true,
        ]);
        $planTributario->services()->createMany([
            ['microservice_id' => $msImpuestos->id],
            ['microservice_id' => $msFactElect->id],
            ['microservice_id' => $msConciliacion->id],
        ]);

        $this->command->info('4 planes contables creados.');
        $this->command->info('');
        $this->command->info('=== Seed completado exitosamente ===');
        $this->command->info('Microservicios: 8');
        $this->command->info('Actividades: ' . Activity::count());
        $this->command->info('Planes: 4');
    }
}
