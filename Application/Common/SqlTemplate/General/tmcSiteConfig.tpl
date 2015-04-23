{* 获得TMC旗舰店数据 *}
SELECT
    tmc.`id`, tmc.`u_id`, tmc.`tmc_code`, tmc.`name`, tmc.`short_name`,
    tmc.`province`, tmc.`city`, tmc.`address`, tmc.`bu_1`, tmc.`bu_2`,
    tmc.`bu_3`, tmc.`bu_4`, tmc.`bu_5`, tmc.`bu_6`, tmc.`bu_7`, tmc.`bu_99`,
    tmc.`contact_name`, tmc.`contact_phone`, tmc.`contact_email`,
    tmc.`hotline`, tmc.`cert_val`, tmc.`license_no`, tmc.`license_date`,
    tmc.`license_period`, tmc.`license_image`, tmc.`legal_name`,
    tmc.`legal_phone`, tmc.`scale`, tmc.`website`, tmc.`status`,
    site.`tmc_id`, site.`style_sheet`, site.`sub_url`, site.`small_logo`,
    site.`large_logo`, site.`reg_agreement`
FROM {$_table_prefix}tmc tmc, {$_table_prefix}tmc_siteconfig site
WHERE tmc.id = site.tmc_id
{*如果不存在status参数，则缺省获取status='0'(即正常)的数据*}
{if $status}
    AND tmc.status = '{$status}'
{else}
    AND tmc.status = '0'
{/if}
{if $tmc_code}
    AND tmc.tmc_code = '{$tmc_code}'
{/if}