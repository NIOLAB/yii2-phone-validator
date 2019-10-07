<?php

namespace udokmeci\yii2PhoneValidator;

use yii\validators\Validator;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;
use Exception;

/**
 * Phone validator class that validates phone numbers for given 
 * country and formats.
 * Country codes and attributes value should be ISO 3166-1 alpha-2 codes
 * @property string $countryAttribute The country code attribute of model
 * @property string $country The country is fixed
 * @property bool $strict If country is not set or selected adds error
 * @property bool $format If phone number is valid formats value with 
 *          libphonenumber/PhoneNumberFormat const (default to INTERNATIONAL)
 */
class PhoneValidator extends Validator
{

    public $strict = true;
    public $countryAttribute;
    public $country;
    public $format = true;

    public function validateAttribute($model, $attribute)
    {
        if ($this->format === true) {
            $this->format = PhoneNumberFormat::INTERNATIONAL;
        }
        // if countryAttribute is set
        if (!isset($country) && isset($this->countryAttribute)) {
            $countryAttribute = $this->countryAttribute;
            $country = $model->$countryAttribute;
        }

        // if country is fixed
        if (!isset($country) && isset($this->country)) {
            $country = $this->country;
        }

        // if none select from our models with best effort
        if (!isset($country) && isset($model->country_code))
            $country = $model->country_code;

        if (!isset($country) && isset($model->country))
            $country = $model->country;


        // if none and strict
        if (!isset($country) && $this->strict) {
            $message = isset($this->message) ? $this->message : \Yii::t('app', 'For phone validation country required');
            $this->addError($model, $attribute, $this->message);
            return false;
        }

        if (!isset($country)) {
            return true;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $numberProto = $phoneUtil->parse($model->$attribute, $country);
            if ($phoneUtil->isValidNumber($numberProto)) {
                if (is_numeric($this->format)) {
                    $model->$attribute = $phoneUtil->format($numberProto, $this->format);
                }
                return true;
            } else {
                $message = isset($this->message) ? $this->message : \Yii::t('app', 'Phone number does not seem to be a valid phone number');
                $this->addError($model, $attribute, $message);
                return false;
            }
        } catch (NumberParseException $e) {
            $message = isset($this->message) ? $this->message :  \Yii::t('app', 'Unexpected Phone Number Format');
            $this->addError($model, $attribute,$message);
        } catch (Exception $e) {
            $message = isset($this->message) ? $this->message :\Yii::t('app', 'Unexpected Phone Number Format or Country Code');
            $this->addError($model, $attribute, $message);
        }
    }

}
