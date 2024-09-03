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
			
			$availableDevices = [
				[
					'name' => 'Govee Light',
					//'Device ID' => '1',
					'InstanzID' => '0',
					//'IPAddress' => '192.168.3.155',
						'create' => [
							'moduleID' => '{E1C6AE31-06E8-74DF-CE5F-6DE9A7AED29D}',
							'configuration' => ['Active' => true]
							]
				]
			];
			

			$count = 0;
			foreach (IPS_GetInstanceListByModuleID('{E1C6AE31-06E8-74DF-CE5F-6DE9A7AED29D}') as $instanceID)
			{
				
				$availableDevices[$count]['instanceID'] = $instanceID;
				$availableDevices[$count]['IPAddress'] = IPS_GetProperty($instanceID,'IPAddress' );
				$availableDevices[$count]['deviceactive'] = IPS_GetProperty($instanceID,'Active' );
				$availableDevices[$count]['timerinterval'] = IPS_GetProperty($instanceID,'Interval' );
				$availableDevices[$count]['name'] = IPS_GetName( $instanceID);	
				$count = $count+1;
			}


			return json_encode([
			
				"actions" => [
					[
						'type' => 'Configurator', 
						'caption'=> 'Govee Konfigurator',
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