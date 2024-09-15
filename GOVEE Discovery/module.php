<?php

declare(strict_types=1);
	class GOVEEDiscovery extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->ConnectParent('{87579ED9-E5BC-EBCD-0095-8D532ECC16BC}');

			$this->SetBuffer("Devices", '{}');

			$this->RegisterTimer("ScanTimer", 0, 'GVL_ScanDevices(' . $this->InstanceID . ');');
		}

		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();

			//if ($this->ReadPropertyBoolean('Active')) 
			{
                $this->ScanDevices();
				$this->SetTimerInterval('ScanTimer', 300 * 1000);
                //$this->SetStatus(102);
            } 
			/*else {
                $this->SetTimerInterval('ScanTimer', 0);
                $this->SetStatus(104);
            }*/

			
			$filter = '.*scan.*';
			$this->SetReceiveDataFilter($filter);
		}

		public function SendData(string $Payload)
		{
			//IPS_LogMessage('Discovery Send', $Payload);
			
			if ($this->HasActiveParent()) 
			{
				$this->SendDataToParent(json_encode([
				
					'DataID' => '{244A8DDD-ECFF-489F-6B91-F436AFAE7115}',
					'Buffer' => $Payload,
					'ClientIP'=> '239.255.255.250',
					'ClientPort'=> 4001,
					'Broadcast' => true,
					'EnableBroadcast' => true
				]));
			}
		}


		public function ReceiveData($JSONString)
        {
        	//IPS_LogMessage('Discovery RECV', $JSONString);
			
			$data = json_decode($JSONString, true); // neune Geräte
			$buffer = json_decode($data['Buffer'], true);
            $new_device = $buffer['msg']['data'];

			$devices = json_decode($this->GetBuffer('Devices'), true); // lese vorhandene Geräte

            if (array_key_exists('device', $new_device)) 
			{
				$devices += [$new_device['device'] => $new_device];
				$this->SetBuffer('Devices', json_encode($devices));
			}

		}



		public function ScanDevices()
        {
			$this->SetBuffer('Devices', '{}');
			
			//IPS_LogMessage('Descvery Send', "Scan Start");
			$govee_message = '{"msg":{"cmd":"scan","data":{"account_topic":"reserve"}}} ';
			$this->SendData($govee_message);

		}


		public function GetConfigurationForm()
		{	
			$this->ScanDevices();
			IPS_Sleep(1000);			

			$newdevices = json_decode( $this->GetBuffer('Devices'), true ) ;
			//IPS_LogMessage('Govee Configurator', $this->GetBuffer('Devices'));
			
			
			$availableDevices = [];
			$count = 0;
			
			foreach($newdevices as $key => $device)
			{
			
				$availableDevices[$count] = 
					[
						'name' =>  'Govee ' . $device['sku'],
						'InstanzID' => '0',
						'DeviceID' => $device['device'],
						'IPAddress' => $device['ip'],
							'create' => [	
								'moduleID' => '{E1C6AE31-06E8-74DF-CE5F-6DE9A7AED29D}',
								'configuration' => ['DeviceID' => $device['device'],
													'IPAddress' => $device['ip'],
													'Active' => true]
								]
					];
				$count = $count+1;
			}
			
			$no_new_devices = $count; 
			
			$count = 0; // 
			foreach (IPS_GetInstanceListByModuleID('{E1C6AE31-06E8-74DF-CE5F-6DE9A7AED29D}') as $instanceID)
			{
				//IPS_LogMessage('Govee Configurator', $instanceID);
				
				$instance_match = false;
				foreach($availableDevices as  $key => $device)
				{	
					if ( ( $availableDevices[$key]['DeviceID'] == IPS_GetProperty($instanceID,'DeviceID') )
					or   ( ( $availableDevices[$key]['IPAddress'] == IPS_GetProperty($instanceID,'IPAddress') ) and (IPS_GetProperty($instanceID,'DeviceID') == ''))) 
					{
						$availableDevices[$key]['instanceID'] = $instanceID;
						$availableDevices[$key]['IPAddress'] = IPS_GetProperty($instanceID,'IPAddress' );
						$availableDevices[$key]['Active'] = IPS_GetProperty($instanceID,'Active' );
						$availableDevices[$key]['timerinterval'] = IPS_GetProperty($instanceID,'Interval' );
						$availableDevices[$key]['name'] = IPS_GetName($instanceID);	
						$instance_match = true;
						//$count = $count+1;
					}
				}	 
				
				IPS_LogMessage('Govee Configurator', 'count'. $count .'no_new_devices'. $no_new_devices);
				
				if (!$instance_match)
				{
				//	$availableDevices[$count + $no_new_devices]['DeviceID'] = IPS_GetProperty($instanceID,'DeviceID' );
				//	$availableDevices[$count + $no_new_devices]['IPAddress'] = IPS_GetProperty($instanceID,'IPAddress' );
				//	$availableDevices[$count + $no_new_devices]['instanceID'] = $instanceID;
				//	$availableDevices[$count + $no_new_devices]['Active'] = IPS_GetProperty($instanceID,'Active' );
				//	$availableDevices[$count + $no_new_devices]['timerinterval'] = IPS_GetProperty($instanceID,'Interval' );
				//	$availableDevices[$count + $no_new_devices]['name'] = IPS_GetName($instanceID);
				}
			
				$count = $count+1;
			
				
			}
			
			if (count($availableDevices) == 0)
			{
				$availableDevices[$count]['name'] = 'no devices found';	
			}
				

			return json_encode([
			
				"actions" => [
					[
						'type' => 'Configurator', 
						'caption'=> 'Govee Konfigurator',
						'delete' => true,
						'columns' => [
								[
									'name' => 'name',
									'caption' => 'Name',
									'width' => 'auto'
								],
								[
									'name' => 'DeviceID',
									'caption' => 'Device Identifier',
									'width' => '200px'
								],
								[
									'name' => 'IPAddress',
									'caption' => 'IP Adress',
									'width' => '150px'
								],
								[
									'name' =>'Active',
									'caption' => 'Active',
									'width' => '150px'
								],
								[
									'name' =>'timerinterval',
									'caption' => 'Timer Interval',
									'width' => '150px'
								]
						],
						'values' => $availableDevices
					]
				]
			]);
		}
	}