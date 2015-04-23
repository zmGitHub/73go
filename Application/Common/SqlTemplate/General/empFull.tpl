{*根据条件获得完整字段(组合了“性别/证件类型/公司名称/部门名称”等)的员工列表*}
SELECT emp.*, d1.d_value sex_disp, d2.d_value id_name, com.name co_name, br.name br_name
    FROM 73go_employee emp
        LEFT OUTER JOIN (SELECT * FROM 73go_dictionary WHERE d_group = 'sex') d1
            ON d1.d_key = emp.sex
        LEFT OUTER JOIN (SELECT * FROM 73go_dictionary WHERE d_group = 'id_type') d2
            ON d2.d_key = emp.id_type
        LEFT OUTER JOIN 73go_company com
            ON com.id = emp.co_id
        LEFT OUTER JOIN 73go_branch br
            ON br.co_id = emp.co_id AND br.id = emp.br_id
WHERE
{*如果不存在status参数，则缺省获取status='1'(即属于企业)的数据*}
{if $status}
    emp.status = '{$status}'
{else}
    emp.status = '1'
{/if}
{if $id}
    AND emp.id = {$id}
{/if}
{if $coId}
    AND emp.co_id = {$coId}
{/if}
{*用于抓取指定部门（不含下级子部门）人员*}
{if $brId}
    AND emp.br_id = {$brId}
{/if}
{*用于抓取指定部门（含下级子部门）人员*}
{if $brList}
    AND emp.br_id in ({$brList})
{/if}