<?php
App::uses('CakeLog', 'Log');

class CertUtils {
  /**
   * Get Certificate Subject DN Environment Value
   * Get Certificate Issuer DN Environment Value
   *
   * @param array Plugin Configuration
   *
   * @return string[]
   * @example ['/CN /O ...', '/CN /O ...']
   */
  public static function getEnvValues($plg_cfg) {
    // Get Certificate SDN and IDN mappings
    $subject_dn_value = "";
    $issuer_dn_value = "";
    $subject_dn_map = !empty($plg_cfg['subject_dn_attribute']) ? $plg_cfg['subject_dn_attribute'] : null;
    $issuer_dn_map = !empty($plg_cfg['issuer_dn_attribute']) ? $plg_cfg['issuer_dn_attribute'] : null;
    // Get Subject DN env value if available
    if(!empty($subject_dn_map)) {
      $subject_dn_value = !empty(getenv($subject_dn_map)) ? getenv($subject_dn_map) : "";
    }
    // Get Issuer DN env value if available
    if(!empty($issuer_dn_map)) {
      $issuer_dn_value = !empty(getenv($issuer_dn_map)) ? getenv($issuer_dn_map) : "";
    }

    return array($subject_dn_value, $issuer_dn_value);
  }

  /**
   * Get Certificate Subject DN Environment Value
   * Get Certificate Issuer DN Environment Value
   *
   * @param array Plugin Configuration
   * @param array Attributes Cached from new OrgIdentity
   *
   * @return string[]
   * @example ['/CN /O ...', '/CN /O ...']
   */
  public static function getEnvValuesFromCache($plg_cfg, $attribute_cache) {
    // Get Certificate SDN and IDN mappings
    $subject_dn_value = "";
    $issuer_dn_value = "";
    $subject_dn_map = !empty($plg_cfg['subject_dn_attribute']) ? $plg_cfg['subject_dn_attribute'] : null;
    $issuer_dn_map = !empty($plg_cfg['issuer_dn_attribute']) ? $plg_cfg['issuer_dn_attribute'] : null;
    // Get Subject DN env value if available
    if(!empty($subject_dn_map)) {
      $subject_dn_value = !empty($attribute_cache[$subject_dn_map]) ? $attribute_cache[$subject_dn_map] : "";
    }
    // Get Issuer DN env value if available
    if(!empty($issuer_dn_map)) {
      $issuer_dn_value = !empty($attribute_cache[$issuer_dn_map]) ? $attribute_cache[$issuer_dn_map] : "";
    }

    return array($subject_dn_value, $issuer_dn_value);
  }

  /**
   * Check if the environmental attribute has value. If this is the case check if it is single valued or multi valued
   *
   * @param string $env_value
   * @return bool|null  true for MULTI valued | false for SINGLE valued | null for NO value
   *
   * @depends Shibboleth SP configuration. Currently we assume that the delimiter is the default one `;`. The semicolon.
   */
  public static function isEnvMultiVal($env_value) {
    if(!empty($env_value)) {
      $env_value_vals = explode(";", $env_value);
      return (count($env_value_vals) > 1) ? true : false;
    }
    return null;
  }

  /**
   * Decide whether we will consume the available Certificate Subject and Issuer DN attributes
   * As a general rulle, if Certificate Subject Dn is MULTI valued ignore Subject Issuer Dn
   *
   * @param array Plugin Configuration
   * @param array Attributes Cached from new OrgIdentity
   *
   * @return bool
   */
  public static function consumeDecideVoPersonCertAttr($plg_cfg, $attribute_cache) {
    // continue = true, means that:
    // 1. we will skip ISSUER handling during Enrollment Flow
    // 2. we will skip ISSUER updated value during login
    $skip_issuer_dn_import = false;
    $issuer_is_multi = false;
    $subject_is_multi = false;

    // Get Certificate SDN and IDN values
    list($subject_value, $issuer_value) = self::getEnvValuesFromCache($plg_cfg, $attribute_cache);
    // Subject DN value type
    $subject_is_multi = self::isEnvMultiVal($subject_value);
    // Issuer DN value type
    $issuer_is_multi = self::isEnvMultiVal($issuer_value);

    if((!is_null($subject_is_multi) && $subject_is_multi)
      || (!is_null($issuer_is_multi) && $issuer_is_multi)) {
      $skip_issuer_dn_import = true;
    }

    return $skip_issuer_dn_import;
  }

}