<?php

/**
 * Class m121019_141942_create_file_table
 */
class m121019_141942_create_file_table extends CDbMigration
{
	public function up()
	{
		$this->createTable(
			\fileProcessor\helpers\FPM::m()->tableName,
			array(
				'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
				'extension' => 'VARCHAR(10) NOT NULL COMMENT "extension of the file"',
				'real_name' => 'VARCHAR(250) NULL DEFAULT NULL COMMENT "real name of the file"',

				'created' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT "creation time"',
			),
			'ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci'
		);
	}

	public function down()
	{
		$this->dropTable(\fileProcessor\helpers\FPM::m()->tableName);
	}
}
