<?php
require_once 'vendor/autoload.php';

use JMS\Serializer\SerializerBuilder;
use Metadata\Driver\FileLocator;
use JMS\Serializer\Metadata\Driver\YamlDriver;
use Metadata\MetadataFactory;
use GoetasWebservices\Xsd\XsdToPhpRuntime\Jms\Handler\XmlSchemaDateHandler;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use FacturaElectronica\FacturaElectronica;
use FacturaElectronica\ImpuestoType;
use FacturaElectronica\IdentificacionType;

$locator = new FileLocator([
    'FacturaElectronica' => __DIR__ . '/config/serializer/FacturaElectronica/',
    'XmlDsig' => __DIR__ . '/config/serializer/XmlDsig/'
]);

$driver = new YamlDriver($locator);
$metadataFactory = new MetadataFactory($driver);

$serializer = SerializerBuilder::create()
    ->addMetadataDir(__DIR__ . '/config/serializer/FacturaElectronica', 'FacturaElectronica')
    ->addMetadataDir(__DIR__ . '/config/serializer/XmlDsig', 'XmlDsig')
    ->configureHandlers(function (HandlerRegistryInterface $registry) {
        $registry->registerSubscribingHandler(new XmlSchemaDateHandler());
    })
    ->build();

$factura = new FacturaElectronica();
$factura->setClave("50614102400310123456700100001010000000015100000091");
$factura->setCodigoActividadEmisor("620101");
$factura->setNumeroConsecutivo("00100001010000000091");
$factura->setFechaEmision(new \DateTime('2025-02-03T14:30:00-06:00'));
$factura->setPlazoCredito(15);
$factura->setCondicionVenta('01');

$emisor = new \FacturaElectronica\EmisorType();
$emisor->setNombre("Tech Solutions S.A.");
$emisorId = new IdentificacionType();
$emisorId->setTipo("01");
$emisorId->setNumero("3102547896");
$emisor->setIdentificacion($emisorId);

$ubicacionEmisor = new \FacturaElectronica\UbicacionType();
$ubicacionEmisor->setProvincia(3);
$ubicacionEmisor->setCanton(1);
$ubicacionEmisor->setDistrito(2);
$ubicacionEmisor->setOtrasSenas("Cerca de la Escuela...");

$emisor->setUbicacion($ubicacionEmisor);

$factura->setEmisor($emisor);


$receptor = new \FacturaElectronica\ReceptorType();
$receptor->setNombre("Innovaciones Digitales Ltda.");
$receptorId = new IdentificacionType();
$receptorId->setTipo("01");
$receptorId->setNumero("205678901");
$receptor->setIdentificacion($receptorId);

$ubicacionReceptor = new \FacturaElectronica\UbicacionType();
$ubicacionReceptor->setProvincia(5);
$ubicacionReceptor->setCanton(1);
$ubicacionReceptor->setDistrito(1);
$ubicacionReceptor->setOtrasSenas("Por la Iglesia Central");
$receptor->setUbicacion($ubicacionEmisor);

$factura->setReceptor($receptor);


$linea1 = new FacturaElectronica\DetalleServicioAType\LineaDetalleAType();
$linea1->setNumeroLinea(1);
$linea1->setDetalle("Consultoría en Desarrollo de Software");
$linea1->setCantidad(1);
$linea1->setPrecioUnitario(250000);
$linea1->setMontoTotal(250000);

$impuesto1 = new ImpuestoType();
$impuesto1->setCodigo('01');
$impuesto1->setCodigoTarifaIVA('08');
$impuesto1->setMonto(20000);
$impuesto1->setTarifa(8.0);

$linea1->setImpuesto([$impuesto1]);
$linea1->setMontoTotalLinea(270000);

$factura->addToDetalleServicio($linea1);

$linea2 = new FacturaElectronica\DetalleServicioAType\LineaDetalleAType();
$linea2->setNumeroLinea(2);
$linea2->setDetalle("Licencia de Software Empresarial");
$linea2->setCantidad(1);
$linea2->setPrecioUnitario(500000);
$linea2->setMontoTotal(500000);

$impuesto2 = new ImpuestoType();
$impuesto2->setCodigo('01');
$impuesto2->setCodigoTarifaIVA('08');
$impuesto2->setMonto(40000);
$impuesto2->setTarifa(8.0);

$linea2->setImpuesto([$impuesto2]);
$linea2->setMontoTotalLinea(540000);

$factura->addToDetalleServicio($linea2);


$resumenFactura = new FacturaElectronica\ResumenFacturaAType();
$resumenFactura->setTotalGravado(750000);
$resumenFactura->setTotalExento(0);
$resumenFactura->setTotalImpuesto(60000);
$resumenFactura->setTotalVenta(750000);
$resumenFactura->setTotalVentaNeta(750000);
$resumenFactura->setTotalComprobante(810000);
$factura->setResumenFactura($resumenFactura);

$xml = $serializer->serialize($factura, 'xml');
echo $xml;
