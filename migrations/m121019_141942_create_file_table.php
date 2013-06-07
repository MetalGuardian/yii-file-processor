<?php

class m121019_141942_create_file_table extends CDbMigration
{
	public function up()
	{
		$this->createTable(
			'{{file}}',
			array(
				'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
				'extension' => 'VARCHAR(10) NOT NULL',
				'real_name' => 'VARCHAR(50) NULL DEFAULT NULL',

				'created' => 'INT UNSIGNED NOT NULL',
			),
			'ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci'
		);
	}

	public function down()
	{
		$this->dropTable('{{file}}');
	}
}
