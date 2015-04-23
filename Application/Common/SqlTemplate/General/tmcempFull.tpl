{*根据条件获得完整字段(组合了“性别/证件类型/公司名称/部门名称”等)的员工列表*}
SELECT emp.*, d1.d_value sex_disp, d2.d_value id_name, tmc.name co_name, br.name br_name
    FROM 73go_tmc_employee emp
        LEFT OUTER JOIN (SELECT * FROM 73go_dictionary WHERE d_group = 'sex') d1
            ON d1.d_key = emp.sex
        LEFT OUTER JOIN (SELECT * FROM 73go_dictionary WHERE d_group = 'id_type') d2
            ON d2.d_key = emp.id_type
        LEFT OUTER JOIN 73go_tmc tmc
            ON tmc.id = emp.tmc_id
        LEFT OUTER JOIN 73go_tmc_branch br
            ON br.tmc_id = emp.tmc_id AND br.id = emp.tmcbr_id
WHERE
{*如果不存在status参数，则缺省获取status='0'的数据*}
{if $status}
    emp.status = '{$status}'
{else}
    emp.status = '0'
{/if}
{if $id}
    AND emp.id = {$id}
{/if}
{if $tmcId}
    AND emp.tmc_id = {$tmcId}
{/if}
{*用于抓取指定部门（不含下级子部门）人员*}
{if $tmcbrId}
    AND emp.tmcbr_id = {$tmcbrId}
{/if}
{*用于抓取指定部门（含下级子部门）人员*}
{if $brList}
    AND emp.tmcbr_id in ({$brList})
{/if}