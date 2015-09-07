<?php

/**
 * LinkField
 *
 * @package silverstripe-linkable
 * @license BSD License http://www.silverstripe.org/bsd-license
 * @author <shea@silverstripe.com.au>
 **/
class LinkField extends TextField{

	/**
	 * @var Boolean
	 **/
	protected $isFrontend = false;

	/**
	 * @var Link
	 **/
	protected $linkObject;


	public static $allowed_actions = array(
		'LinkForm',
		'LinkFormHTML',
		'doSaveLink',
		'doRemoveLink'
	);


	public function Field($properties = array()){
		Requirements::javascript(LINKABLE_PATH . '/javascript/linkfield.js');
		return parent::Field();
	}


	/**
	 * The LinkForm for the dialog window
	 *
	 * @return Form
	 **/
	public function LinkForm(){
		$link = $this->getLinkObject();

		$action = FormAction::create('doSaveLink', _t('Linkable.SAVE', 'Save'))->setUseButtonTag('true');

		if(!$this->isFrontend){
			$action->addExtraClass('ss-ui-action-constructive')->setAttribute('data-icon', 'accept');
		}

		$link = null;
		if($serialized = $this->request->getVar('LinkSerialized')){
			$link = new Link(unserialize($serialized));
		}
		$link = $link ? $link : singleton('Link');

		$fields = $link->getCMSFields();

		$title = $link ? _t('Linkable.EDITLINK', 'Edit Link') : _t('Linkable.ADDLINK', 'Add Link');
		$fields->insertBefore(HeaderField::create('LinkHeader', $title), _t('Linkable.TITLE', 'Title'));
		$actions = FieldList::create($action);
		$form = Form::create($this, 'LinkForm', $fields, $actions);

		if($link){
			$form->loadDataFrom($link);
		}

		$this->owner->extend('updateLinkForm', $form);

		return $form;
	}


	/**
	 * Either updates the current link or creates a new one
	 * Returns field template to update the interface
	 * @return String
	 **/
	public function doSaveLink($data, $form){
		$link = $this->getLinkObject() ? $this->getLinkObject() : Link::create();
		$form->saveInto($link);
		$this->setValue(serialize($link->toMap()));
		$this->setForm($form);
		return $this->FieldHolder();
	}


	/**
	 * Delete link action - TODO
	 *
	 * @return String
	 **/
	public function doRemoveLink(){
		$this->setValue('');
		return $this->FieldHolder();
	}


	/**
	 * Returns the current link object
	 *
	 * @return Link
	 **/
	public function getLinkObject(){
		$serialized = Controller::curr()->request->requestVar('LinkSerialized');
		if(!$this->linkObject){
			$serialized = $this->Value() ? $this->Value() : $serialized;
			if($serialized){
				$this->linkObject = new Link(unserialize($serialized));
			}
		}
		return $this->linkObject;
	}


	/**
	 * Returns the HTML of the LinkForm for the dialog
	 *
	 * @return String
	 **/
	public function LinkFormHTML(){
		return $this->LinkForm()->forTemplate();
	}


	public function getIsFrontend(){
		return $this->isFrontend;
	}


	public function setIsFrontend($bool){
		$this->isFrontend = $bool;
		return $this->this;
	}
}