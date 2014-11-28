<?php
	namespace Helpers;

	use Phalcon\Forms\Form,
		Phalcon\Forms\Element,
		Models\ShopsDeliveries,
		Models\ShopsPayments,
		Models\ShopsRegions;

	/**
	 * Class OrderForm Форма для осуществления заказа
	 * @package Phalcon
	 * @subpackage Helpers
	 */
	class OrderForm extends Form
	{

		private

			$_regions	=	false,

			$_delivery	=	false,

			$_payment	=	false;


		/**
		 * Инициализация полей формы
		 */
		public function initialize($obj = null, $options)
		{
			// инициализация сущностей
			$this->setEntity($this);


			$this->_regions		=	$this->getRegionsService($options['country']);

			$this->_delivery	=	$this->getDeliveryService($options['delivery']);

			$this->_payment		=	$this->getPaymentService($options['payment']);

			$this->add(new Element\Text("user[fio]", [
					'id'			=>	'user[fio]',
					'class'			=>	'wide required',
					'required'		=>	'true',
				])
			);

			$this->add(new Element\Email("user[email]", [
					'id'			=>	'user[email]',
					'class'			=>	'wide',
					'pattern'		=>	"[^@]+@[^@]+\.[a-zA-Z]{2,6}",
				])
			);

			$this->add(new Element\Text("user[phones][0]", [
					'id'			=>	'user[phones_m]',
					'class'			=>	'required',
					'required'		=>	'true',
				])
			);

			$this->add(new Element\Text("user[phones][1]", [
					'id'			=>	'user[phones_h]',
				])
			);

			$this->add(new Element\Text("user[phones][2]", [
					'id'			=>	'user[phones_a]',
				])
			);

			$this->add(new Element\Text("user[address][postal_code]", [
					'id'			=>	'user[address][postal_code]',
					'size'			=>	6,
				])
			);

			$this->add(new Element\Text("user[address][city]", [
					'id'			=>	'user[address][city]',
					'class'			=>	'wide',
				])
			);

			$this->add(new Element\Text("user[address][address]", [
					'id'			=>	'user[address][address]',
					'class'			=>	'wide',
				])
			);

			// Загрузка регионов в select

			$defaultRegion = (isset($options['select']['user']['address']['region']))
				? $options['select']['user']['address']['region'] : key($this->_regions);

			$this->add((new Element\Select("user[address][region]", $this->_regions))
				->setDefault($defaultRegion));

			$defaultDelivery = (isset($options['select']['options']['delivery_id']))
							? $options['select']['options']['delivery_id'] : key($this->_delivery);

			// Загрузка сервисов доставки в select
			$this->add((new Element\Select("options[delivery_id]", $this->_delivery))
				->setDefault($defaultDelivery));

			$defaultPayment = (isset($options['select']['options']['payment_id']))
				? $options['select']['options']['payment_id'] : key($this->_payment);

			// Загрузка сервисов оплаты в select
			$this->add((new Element\Select("options[payment_id]", $this->_payment))
				->setDefault($defaultPayment));



			$this->add(new Element\Text("coupon_uniqid", [
					'id'			=>	'coupon_uniqid',
				])
			);

			$this->add(new Element\TextArea("comment", [
					'id'			=>	'comment',
					'class'			=>	'wide',
				])
			);

			$this->add(new Element\Check("subscribe", [
					'id'			=>	'subscribe',
					'checked'		=>	'checked',
				])
			);

			$this->add(new Element\Submit("save", [
					'id'			=>	'save',
					'class'			=>	'button'
				])
			);
		}

		/**
		 * Этот метод возвращает значение по умолчанию для поля 'csrf'
		 * @access public
		 * @return string
		 */
		public function getCsrf()
		{
			return $this->security->getToken();
		}

		/**
		 * Обработка регионов
		 *
		 * @param int $region_id
		 * @access private
		 * @return array
		 */
		private function getRegionsService($region_id)
		{
			$regionServices = (new ShopsRegions())->get(['region AS id', 'region'], ['country_code' => $region_id], null, 90);
			$data = Catalogue::arrayToPair($regionServices);
			return (!empty($data)) ? $data : [];
		}

		/**
		 * Обработка служб доставки
		 *
		 * @param mixed $delivery_ids
		 * @access private
		 * @return array
		 */
		private function getDeliveryService($delivery_ids)
		{
			$delivery_ids	=	explode(",", $delivery_ids);
			$deliveryServices = (new ShopsDeliveries())->get(['id', 'title'], ['id' => $delivery_ids], ['sort' => 'ASC'], 25);
			$data = Catalogue::arrayToPair($deliveryServices);
			return (!empty($data)) ? $data : [];
		}

		/**
		 * Обработка служб оплаты
		 *
		 * @param mixed $payment_ids
		 * @access private
		 * @return array
		 */
		private function getPaymentService($payment_ids)
		{
			$payment_ids	=	explode(",", $payment_ids);
			$paymentServices = (new ShopsPayments())->get(['id', 'title'], ['id' => $payment_ids], ['sort' => 'ASC'], 25);
			$data = Catalogue::arrayToPair($paymentServices);
			return (!empty($data)) ? $data : [];
		}
	}