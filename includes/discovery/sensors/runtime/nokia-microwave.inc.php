<?php

$data = SnmpQuery::walk([
    'OPTICSIM-RADIO-PM-MIB::opticsIMAdaptiveModulationCurrentDataTable',
    'OPTICSIM-RADIO-TRS-PDH-MIB::radioPDHTTPBidUserLabel',
])->valuesByIndex();

foreach ($data as $index => $entry) {
    
    //Explode the Index to extract the base index
    $baseIndex = explode('.', $index);

    // If the Table is exists, using the subindex[1] of 1 and the data is valid based on RowStatus
    if (isset($entry['OPTICSIM-RADIO-PM-MIB::pmAdaptiveModulationCDUsageTime4QAM']) && $baseIndex[1] == '1' && $entry['OPTICSIM-RADIO-PM-MIB::pmAdaptiveModulationCDRowStatus'] == 1 ) {
        //Discover Radio Runtime
        $oid4Qam = '.1.3.6.1.4.1.637.54.1.10.5.1.9.1.6.' . $index;
        $oid8Qam = '1.3.6.1.4.1.637.54.1.10.5.1.9.1.10.' . $index;
        $oid16Qam = '.1.3.6.1.4.1.637.54.1.10.5.1.9.1.7.' . $index;
        $oid32Qam = '.1.3.6.1.4.1.637.54.1.10.5.1.9.1.11.' . $index;
        $oid64Qam = '.1.3.6.1.4.1.637.54.1.10.5.1.9.1.8.' . $index;
        $oid128Qam = '.1.3.6.1.4.1.637.54.1.10.5.1.9.1.12.' . $index;
        $oid256Qam = '.1.3.6.1.4.1.637.54.1.10.5.1.9.1.13.' . $index;
        $oid512Qam = '.1.3.6.1.4.1.637.54.1.10.5.1.9.1.14.' . $index;
        $oid1024Qam = '.1.3.6.1.4.1.637.54.1.10.5.1.9.1.15.' . $index;
        $oid2048Qam = '.1.3.6.1.4.1.637.54.1.10.5.1.9.1.16.' . $index;
        $oid4096Qam = '.1.3.6.1.4.1.637.54.1.10.5.1.9.1.17.' . $index; 

        // Pull the Values
        $current4Qam = $data[$index]['OPTICSIM-RADIO-PM-MIB::pmAdaptiveModulationCDUsageTime4QAM'] ?? null;
        $current8Qam = $data[$index]['OPTICSIM-RADIO-PM-MIB::pmAdaptiveModulationCDUsageTime8QAM'] ?? null;
        $current16Qam = $data[$index]['OPTICSIM-RADIO-PM-MIB::pmAdaptiveModulationCDUsageTime16QAM'] ?? null;
        $current32Qam = $data[$index]['OPTICSIM-RADIO-PM-MIB::pmAdaptiveModulationCDUsageTime32QAM'] ?? null;
        $current64Qam = $data[$index]['OPTICSIM-RADIO-PM-MIB::pmAdaptiveModulationCDUsageTime64QAM'] ?? null;
        $current128Qam = $data[$index]['OPTICSIM-RADIO-PM-MIB::pmAdaptiveModulationCDUsageTime128QAM'] ?? null;
        $current256Qam = $data[$index]['OPTICSIM-RADIO-PM-MIB::pmAdaptiveModulationCDUsageTime256QAM'] ?? null;
        $current512Qam = $data[$index]['OPTICSIM-RADIO-PM-MIB::pmAdaptiveModulationCDUsageTime512QAM'] ?? null;
        $current1024Qam = $data[$index]['OPTICSIM-RADIO-PM-MIB::pmAdaptiveModulationCDUsageTime1024QAM'] ?? null;
        $current2048Qam = $data[$index]['OPTICSIM-RADIO-PM-MIB::pmAdaptiveModulationCDUsageTime2048QAM'] ?? null;
        $current4096Qam = $data[$index]['OPTICSIM-RADIO-PM-MIB::pmAdaptiveModulationCDUsageTime4096QAM'] ?? null;

        // Pull User Label for Radio
        $label = $data[$baseIndex[0]]['OPTICSIM-RADIO-TRS-PDH-MIB::radioPDHTTPBidUserLabel'] ?? null;

        //Split the Index so we can reference different digits
        $indexArray = str_split($baseIndex[0]);

        // Define descr as something sane as a fall through
        $port_descr = ' ' . $index;

        // Decode the ifIndex into human readable ifDescr
        // Make sure its a Radio ID that starts with a '5'
        if( $indexArray[0] == '5' ) {

            // For basic interfaces that the second digit is a '0'
            if( $indexArray[1] == '0' && ( count($indexArray) == 5 ) ) {
                $port_descr = ' Slot:' . $indexArray[2] . ' Port:' . $indexArray[4] . ' Ch:' . $indexArray[3];
            }

            // UBT-T interfaces where the second digit is '1'
            if( $indexArray[1] == '1' && ( count($indexArray) == 5 ) ) {

                // If the radio is a Primary when the 3rd digit is '1'
                if ( $indexArray[2] == '1' ) {
                    $port_descr = ' Primary Slot:' . $indexArray[3] . ' Port:' . $indexArray[4];
                }

                // If the radio is a Spare when the 3rd digit is '0'
                if ( $indexArray[2] == '0' ) {
                    $port_descr = ' Spare Slot:' . $indexArray[3] . ' Port:' . $indexArray[4];
                }
            }

            // For 6 digit IDs
            if( count($indexArray) == 6 ) {
                $port_descr = ' Slot:' . $indexArray[1] .  ' Port:' . $indexArray[2] . ' Ch:' . $indexArray[3];
            }

        }

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'runtime',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oid4Qam,
            'sensor_index' => $index,
            'sensor_type' => '4QamRuntime',
            'sensor_descr' => $label . $port_descr . ' Time in 4QAM',
            'sensor_divisor' => 900000,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $current4Qam/900000,
            'entPhysicalIndex' => $baseIndex[0],
            'entPhysicalIndex_measured' => 'ports',
            'sensor_group' => 'Last 15 Minutes'
        ]));

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'runtime',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oid8Qam,
            'sensor_index' => $index,
            'sensor_type' => '8QamRuntime',
            'sensor_descr' => $label . $port_descr . ' Time in 8QAM',
            'sensor_divisor' => 900000,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $current8Qam/900000,
            'entPhysicalIndex' => $baseIndex[0],
            'entPhysicalIndex_measured' => 'ports',
            'sensor_group' => 'Last 15 Minutes'
        ]));

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'runtime',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oid16Qam,
            'sensor_index' => $index,
            'sensor_type' => '16QamRuntime',
            'sensor_descr' => $label . $port_descr . ' Time in 16QAM',
            'sensor_divisor' => 900000,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $current16Qam/900000,
            'entPhysicalIndex' => $baseIndex[0],
            'entPhysicalIndex_measured' => 'ports',
            'sensor_group' => 'Last 15 Minutes'
        ]));

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'runtime',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oid32Qam,
            'sensor_index' => $index,
            'sensor_type' => '32QamRuntime',
            'sensor_descr' => $label . $port_descr . ' Time in 32QAM',
            'sensor_divisor' => 900000,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $current32Qam/900000,
            'entPhysicalIndex' => $baseIndex[0],
            'entPhysicalIndex_measured' => 'ports',
            'sensor_group' => 'Last 15 Minutes'
        ]));

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'runtime',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oid64Qam,
            'sensor_index' => $index,
            'sensor_type' => '64QamRuntime',
            'sensor_descr' => $label . $port_descr . ' Time in 64QAM',
            'sensor_divisor' => 900000,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $current64Qam/900000,
            'entPhysicalIndex' => $baseIndex[0],
            'entPhysicalIndex_measured' => 'ports',
            'sensor_group' => 'Last 15 Minutes'
        ]));

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'runtime',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oid128Qam,
            'sensor_index' => $index,
            'sensor_type' => '128QamRuntime',
            'sensor_descr' => $label . $port_descr . ' Time in 128QAM',
            'sensor_divisor' => 900000,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $current128Qam/900000,
            'entPhysicalIndex' => $baseIndex[0],
            'entPhysicalIndex_measured' => 'ports',
            'sensor_group' => 'Last 15 Minutes'
        ]));

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'runtime',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oid256Qam,
            'sensor_index' => $index,
            'sensor_type' => '256QamRuntime',
            'sensor_descr' => $label . $port_descr . ' Time in 256QAM',
            'sensor_divisor' => 900000,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $current256Qam/900000,
            'entPhysicalIndex' => $baseIndex[0],
            'entPhysicalIndex_measured' => 'ports',
            'sensor_group' => 'Last 15 Minutes'
        ]));

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'runtime',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oid512Qam,
            'sensor_index' => $index,
            'sensor_type' => '512QamRuntime',
            'sensor_descr' => $label . $port_descr . ' Time in 512QAM',
            'sensor_divisor' => 900000,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $current512Qam/900000,
            'entPhysicalIndex' => $baseIndex[0],
            'entPhysicalIndex_measured' => 'ports',
            'sensor_group' => 'Last 15 Minutes'
        ]));

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'runtime',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oid1024Qam,
            'sensor_index' => $index,
            'sensor_type' => '1024QamRuntime',
            'sensor_descr' => $label . $port_descr . ' Time in 1024QAM',
            'sensor_divisor' => 900000,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $current1024Qam/900000,
            'entPhysicalIndex' => $baseIndex[0],
            'entPhysicalIndex_measured' => 'ports',
            'sensor_group' => 'Last 15 Minutes'
        ]));

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'runtime',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oid2048Qam,
            'sensor_index' => $index,
            'sensor_type' => '2048QamRuntime',
            'sensor_descr' => $label . $port_descr . ' Time in 2048QAM',
            'sensor_divisor' => 900000,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $current2048Qam/900000,
            'entPhysicalIndex' => $baseIndex[0],
            'entPhysicalIndex_measured' => 'ports',
            'sensor_group' => 'Last 15 Minutes'
        ]));

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'runtime',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oid4096Qam,
            'sensor_index' => $index,
            'sensor_type' => '4096QamRuntime',
            'sensor_descr' => $label . $port_descr . ' Time in 4096QAM',
            'sensor_divisor' => 900000,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $current4096Qam/900000,
            'entPhysicalIndex' => $baseIndex[0],
            'entPhysicalIndex_measured' => 'ports',
            'sensor_group' => 'Last 15 Minutes'
        ]));

    }
}

