<?php

namespace App\Domain\Enums;

enum ServiceType: string
{
    case URGENCIAS = 'Urgencias';
    case CONSULTA_EXTERNA = 'Consulta Externa';
    case LABORATORIO = 'Laboratorio';
    case IMAGENES_DIAGNOSTICAS = 'Imágenes Diagnósticas';
    case VACUNACION = 'Vacunación';
    case ODONTOLOGIA = 'Odontología';
    case TRANSPORTE_ASISTENCIAL_BASICO = 'Transporte Asistencial Básico';
    case ALMACEN = 'Almacén';
    case SALA_DE_PARTO = 'Sala de parto';
    case HOSPITALIZACION = 'Hospitalización';
    case ESTERILIZACION = 'Esterilización';

    // Opcional: un método para obtener todos los valores para un select
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}