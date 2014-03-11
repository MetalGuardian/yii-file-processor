Install
-------
in config/main.php:

		Yii::setPathOfAlias('fileProcessor', '/path/to/extension/');

example for standard yii structure when this module located in extension dir:

		Yii::setPathOfAlias('fileProcessor', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'yii-file-processor' . DIRECTORY_SEPARATOR . 'fileProcessor');

application config:

		...
		'controllerMap' => array(
			'image' => array(
				'class' => '\fileProcessor\controllers\ImageController',
			),
		),
		...

if you merge main config with console config, you need unset controllerMap key


modules section:

		'file-processor' => array(
			'baseDir' => realpath(
					__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'www'
				) . DIRECTORY_SEPARATOR,
			'imageSections' => array(
				'admin' => array(
					'default' => array(
						'width' => 100,
						'height' => 100,
						'quality' => 100,
						'do' => 'resize', // resize|adaptiveResize
					),
				),
			),
			'imageHandler' => array(
				'driver' => '\fileProcessor\extensions\imageHandler\drivers\MDriverGD',
				// '\fileProcessor\extensions\imageHandler\drivers\MDriverImageMagic'
			),
		),


component section:

		'urlManager'=>array(
			...
			'rules'=>array(
				...
				array(
					'class' => '\fileProcessor\components\YiiFileProcessorUrlRule',
					'connectionId' => 'db',
					'cacheId' => 'cache',
					'controllerId' => 'image',
				),
				// controllerId - name of the controller, which you set in controller map
				...
			),
		),

add behavior to the model:

		'fileBehavior' => array(
			'class' => '\fileProcessor\components\FileUploadBehavior',
			'attributeName' => 'file_id',
			'fileTypes' => 'png, gif, jpeg, jpg',
		),

Run command:

		php protected/yiic.php migrate --migrationPath=application.extensions.yii-file-processor.fileProcessor.migrations
