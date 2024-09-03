<?php

declare(strict_types=1);
	class GOVEESplitter extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();

			//$this->ConnectParent('{82347F20-F541-41E1-AC5B-A636FD3AE2D8}'); // UBD Prot
			$this->ForceParent('{82347F20-F541-41E1-AC5B-A636FD3AE2D8}'); // UBD Prot
			//$this->ForceParent('{BAB408E0-0A0F-48C3-B14E-9FB2FA81F66A}'); // Mulicast Port anfordern
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
			$this->GetConfigurationForParent();

		 	$config = json_decode( IPS_GetConfiguration(IPS_GetInstance($this->InstanceID)['ConnectionID']), true);
			//print_r($config);
			$this->SetSummary($config['BindIP'] .".". $config['BindPort']);

			$this->SetStatus(102);

		}

		public function GetConfigurationForParent()
        {
			$settings = [
                'BindPort'           => 4002,
				'BindIP'           => '0.0.0.0',
                'EnableBroadcast'    => false,
                'EnableReuseAddress' => false,
                'Host'               => '',
                'Port'               => 4001,
				"Open"				=> true
            ];

            return json_encode($settings, JSON_UNESCAPED_SLASHES);
        }
	
	
		public function ForwardData($JSONString)
		{
			$data = json_decode($JSONString);
			IPS_LogMessage('Splitter FRWD', utf8_decode($data->Buffer . ' - ' . $data->ClientIP . ' - ' . $data->ClientPort));

			$this->SendDataToParent(json_encode([
		
				//'DataID' => '{C8792760-65CF-4C53-B5C7-A30FCC84FEFE}', // Multicast
				'DataID' => '{8E4D9B23-E0F2-1E05-41D8-C21EA53B8706}', // UDP
				'Buffer' => $data->Buffer, 
				
				'ClientIP' => $data->ClientIP,
            	'ClientPort' => $data->ClientPort,
				'EnableBroadcast' => true,
				'Broadcast' => $data->Broadcast
				
				]));

			return 'String data for device instance!';
		}

		public function ReceiveData($JSONString)
		{
		
			$data = json_decode($JSONString);
			//IPS_LogMessage('Splitter RECV', $data->Buffer . ' - ' . $data->ClientIP . ' - ' . $data->ClientPort);

			$this->SendDataToChildren(json_encode(
					[
						'DataID' => '{D8E76447-9EC7-BCE8-8DE2-473BB8EC6379}', 
						'Buffer' => $data->Buffer, 
						'ClientIP' => $data->ClientIP, 
						'ClientPort' => $data->ClientPort
					]
				));
		}
	
	}