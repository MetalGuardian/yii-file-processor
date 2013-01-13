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
		'imageSections' => array(
			'admin'=>array(
				'default'=>array(
					'width' => 100,
					'height' => 100,
					'quality' => 100,
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
			'uploads/thumb/<sub:\d+>/<model:\w+>_<type:\w+>/<id:\d+>.<ext:(png|gif|jpg|jpeg)>' => 'image/resize',
			// uploads/thumb/ - it is path of cached image files will be saved
			// image - name of the controller, which you set in controller map
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