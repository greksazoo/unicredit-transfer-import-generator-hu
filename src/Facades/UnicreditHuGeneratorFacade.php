<?php
	
	namespace Greksazoo\UnicreditTransferImportGeneratorHu\Facades;
	
	use Illuminate\Support\Facades\Facade;
	
	class UnicreditHuGeneratorFacade extends Facade
	{
		protected static function getFacadeAccessor()
		{
			return 'unicredithugenerator';
		}
	}
