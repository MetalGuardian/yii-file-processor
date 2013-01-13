<?php
namespace fileProcessor\models;
/**
 * Author: Ivan Pushkin
 * Email: metal@vintage.com.ua
 */
/**
 * This is the model class for table "{{file}}".
 *
 * The followings are the available columns in table '{{file}}':
 * @property string $id
 * @property string $extension
 * @property string $real_name
 * @property string $created
 */
class File extends \fileProcessor\components\base\FileProcessorModuleActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return File the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{image}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('extension, created', 'required'),
			array('extension, created', 'length', 'max'=>10),
			array('real_name', 'length', 'max'=>50),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, extension, real_name, created', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => \fileProcessor\helpers\FPM::t('ID'),
			'extension' => \fileProcessor\helpers\FPM::t('Extension'),
			'real_name' => \fileProcessor\helpers\FPM::t('Real Name'),
			'created' => \fileProcessor\helpers\FPM::t('Created'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * @param bool|integer $pageSize
	 *
	 * @return \CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search($pageSize = false)
	{
		$criteria=new \CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('extension',$this->extension,true);
		$criteria->compare('real_name',$this->real_name,true);
		$criteria->compare('created',$this->created,true);

		return new \CActiveDataProvider($this, array(
			'criteria' => $criteria,
			'pagination' => array(
				'pageSize' => $pageSize ? $pageSize : \fileProcessor\helpers\FPM::m()->defaultPageSize,
			),
			'sort' => array(
				'defaultSort' => array(
					'created' => \CSort::SORT_DESC,
				),
			),
		));
	}
}
