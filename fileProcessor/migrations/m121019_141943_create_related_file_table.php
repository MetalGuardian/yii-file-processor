<?php
use fileProcessor\helpers\FPM;

/**
 * Class m121019_141943_create_related_file_table
 */
class m121019_141943_create_related_file_table extends CDbMigration
{
	public function up()
	{
		$this->createTable(
			FPM::m()->relatedTableName,
			array(
				'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
				'file_id' => 'INT UNSIGNED NOT NULL COMMENT "file id"',
				'model_class' => 'VARCHAR(250) NOT NULL COMMENT "model class for file related for"',
				'model_id' => 'INT UNSIGNED NOT NULL COMMENT "model entity id for file related for"',

				'visible' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT "visibility of the file"',
				'position' => 'INT UNSIGNED NOT NULL DEFAULT 0 COMMENT "position of the file; greater is higher"',

				'INDEX key_model_id_model_class (model_id, model_class)',
				'INDEX key_model_id (model_id)',
				'INDEX key_model_class (model_class)',

				'CONSTRAINT fk_related_file_file_id_to_file_id FOREIGN KEY (file_id) REFERENCES ' . FPM::m()->tableName . ' (id) ON DELETE CASCADE ON UPDATE CASCADE',
			),
			'ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 COLLATE=utf8_unicode_ci'
		);
	}

	public function down()
	{
		$this->dropTable(FPM::m()->relatedTableName);
	}
}
