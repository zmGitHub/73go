SELECT distinct tqr.id tqrid,qr.id qrid,tqr.*,qr.*,te.`name`,
       com.short_name,emp.name as emp_name, vt.vip_level, odr.id, odr.order_num,tqr.`status` as rec_status
FROM 73go_tmc_qsx_rec tqr
	LEFT JOIN 73go_qsx_req qr ON tqr.req_id=qr.id
	LEFT JOIN 73go_tmc_employee te ON tqr.rec_emp_id=te.id
	LEFT JOIN 73go_tmc tmc ON te.tmc_id=tmc.id  
	LEFT JOIN 73go_user u ON qr.u_id=u.id
	LEFT JOIN 73go_employee emp ON emp.u_id=qr.u_id
	LEFT JOIN 73go_company com ON emp.co_id=com.id
	LEFT JOIN 73go_vip_table vt ON vt.tmc_id = tmc.id and vt.emp_id=emp.id
	LEFT JOIN 73go_qsx_solution sol ON sol.req_id = qr.id 
	LEFT JOIN 73go_order odr ON (odr.src = '1' AND odr.src_id = sol.id) 
WHERE 1 = 1
{*处理$status和$status_ne参数*}
{if $status}
	AND tqr.status = '{$status}'
{elseif $status_ne}
	AND tqr.status != '{$status_ne}'
{/if}
{*qr_searching表示要抓取“处理完”的数据*}
{if $qr_searching}
	AND 
	 ((qr.`status` = '2' OR qr.`status` = '3') OR
	  (qr.solu_id IS NOT NULL AND sol.`status`='1' AND sol.u_id != {$u_id} ) OR
	  ( sol.`status`='1' AND sol.u_id = {$u_id} AND odr.order_num IS NOT NULL AND odr.order_num <> '' AND sol.u_id in ({$u_idlist})))
{/if}
{*$bygrp 1=>同组, 2=>本人, 3=>以上都是*}
{if $bygrp}
and (
{if $bygrp eq 1 or $bygrp eq 3}
	tqr.rec_emp_id in ({$emp_idlist}) 
{/if}
{if $bygrp eq 3}
		OR
{/if}
{if $bygrp eq 2 or $bygrp eq 3}
		tqr.rec_emp_id =(select b.id from 73go_tmc_employee b where b.u_id={$u_id})
{/if}
	  )
{/if}	   
ORDER BY vip_level desc,qr.submit_time desc
{if $Page} 
limit {$Page->firstRow} , {$Page->listRows}
{/if}


