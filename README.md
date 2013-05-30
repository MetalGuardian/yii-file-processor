Install
-------
in config/main.php:

		Yii::setPathOfAlias('fileProcessor', '/path/to/extension/');

example for standard yii structure when this module located in extension dir:

		Yii::setPathOfAlias('fileProcessor', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'yii-file-processor');

application config:

		...
		'controllerMap'=>array(
			'image' => array(
				'class'=>'\fileProcessor\controllers\ImageController',
			),
		),
		...

if you merge main config with console config, you need unset controllerMap key


modules section:

		'file-processor'=>array(
			'class' => '\fileProcessor\FileProcessorModule',
			'originalBaseDir' => 'uploads/original',
			'cachedImagesBaseDir' => 'uploads/thumb',
			// set path without first and last slashes
			'imageSections' => array(
				'admin'=>array(
					'default'=>array(
						'width' => 100,
						'height' => 100,
						'quality' => 100,
						'do' => 'thumb', // resize|thumb|adaptiveThumb
					),
				),
			),
			'imageHandler' => array(
				'driver' => \fileProcessor\extensions\imageHandler\MImageHandler::IMAGE_MAGIC_DRIVER,
				// \fileProcessor\extensions\imageHandler\MImageHandler::GD_DRIVER
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
				'uploads/thumb/<sub:\d+>/<model:\w+>_<type:\w+>/<id:\d+>.<ext:(png|gif|jpg|jpeg)>' => 'image/resize',
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

		php protected/yiic.php migrate --migrationPath=application.extensions.yii-file-processor.migrations
