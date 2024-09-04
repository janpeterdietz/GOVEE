<?php

declare(strict_types=1);
	class GOVEEKonfigurator extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
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
		}

	

		public function GetConfigurationForm()
		{	
			// hier müsste wohl Scan Device rein??
			//IPS_LogMessage('Govee Configurator', GVL_GetDevices(34857));

			foreach (IPS_GetInstanceListByModuleID('{7B56B1ED-9DC0-3879-DF12-2635C582BDBE}') as $instanceID)
			{
				$discoveryID = $instanceID;
			}

			//IPS_LogMessage('Konfigurator',  $discoveryID);

			$newdevices = json_decode( GVL_GetDevices($discoveryID), true);
		
			//IPS_LogMessage('Konfigurator', print_r( $newdevices));
			
			$count = 0;
			foreach($newdevices as $device)
			{
    			//IPS_LogMessage('Govee Configurator', $device['ip']);
			
				$availableDevices[$count] = 
					[
						'name' =>  'Govee ' . $device['sku'],
						'InstanzID' => '0',
						'IPAddress' => $device['ip'],
							'create' => [	
								'moduleID' => '{E1C6AE31-06E8-74DF-CE5F-6DE9A7AED29D}',
								'configuration' => ['IPAddress' => $device['ip'],
													'Active' => true]
								]
					];
				$count = $count+1;
			}
			

			$count = 0;
			foreach (IPS_GetInstanceListByModuleID('{E1C6AE31-06E8-74DF-CE5F-6DE9A7AED29D}') as $instanceID)
			{
				$count = 0;
				foreach($availableDevices as  $device)
				{	
					if ( $availableDevices[$count]['IPAddress'] == IPS_GetProperty($instanceID,'IPAddress') )
					{
						$availableDevices[$count]['instanceID'] = $instanceID;
						$availableDevices[$count]['deviceactive'] = IPS_GetProperty($instanceID,'Active' );
						$availableDevices[$count]['timerinterval'] = IPS_GetProperty($instanceID,'Interval' );
						$availableDevices[$count]['name'] = IPS_GetName($instanceID);	
					}
					$count = $count+1;
				}
				
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
									'name' => 'IPAddress',
									'caption' => 'IP Adress',
									'width' => '150px'
								],
								[
									'name' =>'deviceactive',
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