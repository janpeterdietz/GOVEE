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
								'name' => 'ipadress',
								'caption' => 'IP Adress',
								'width' => '150px'
							],
							[
								'name' =>'deviceID',
								'caption' => 'Device ID',
								'width' => '150px'
							]
						]
					]
				]
			]);
		}
	}