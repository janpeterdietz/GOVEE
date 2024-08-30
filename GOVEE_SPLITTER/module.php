<?php

declare(strict_types=1);
	class GOVEE_SPLITTER extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->ForceParent('{82347F20-F541-41E1-AC5B-A636FD3AE2D8}'); //UDP Port anfordern
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
			$this->GetConfigurationForParent(); // UDP Port konfigurieren

			// SetSummary setzen
			$config = json_decode( IPS_GetConfiguration(IPS_GetInstance($this->InstanceID)['ConnectionID']), true);
			$this->SetSummary($config['BindIP'] .":". $config['BindPort']);
		}

		public function GetConfigurationForParent() //Set UBD Port
        {
            $settings = [
                'BindPort'           => 4002,
				'BindIP'           => '0.0.0.0',
                'EnableBroadcast'    => false,
                'EnableReuseAddress' => false,
                'Host'               => '',
                'Port'               => 4003,
				//"Open"				=> true
            ];

            return json_encode($settings, JSON_UNESCAPED_SLASHES);
		}

  
	
		public function ForwardData($JSONString)
		{
			$data = json_decode($JSONString);
			//IPS_LogMessage('Splitter FRWD', utf8_decode($data->Buffer . ' - ' . $data->ClientIP . ' - ' . $data->ClientPort));

			$this->SendDataToParent(json_encode(
				[
				'DataID' => '{8E4D9B23-E0F2-1E05-41D8-C21EA53B8706}', 
				'Buffer' => $data->Buffer, 
				'ClientIP' => $data->ClientIP,
            	'ClientPort' => $data->ClientPort,
				'Broadcast' => false
				]));

			return 'String data for device instance!';
		}

		public function ReceiveData($JSONString)
		{
		
			$data = json_decode($JSONString);
			//IPS_LogMessage('Splitter RECV', $data->Buffer . ' - ' . $data->ClientIP . ' - ' . $data->ClientPort);

			$this->SendDataToChildren(json_encode(
					[
						'DataID' => '{1EF4729A-A536-49DC-57F5-6DB8E2E723A2}', 
						'Buffer' => $data->Buffer, 
						'ClientIP' => $data->ClientIP, 
						'ClientPort' => $data->ClientPort
					]
				));
		}
	}