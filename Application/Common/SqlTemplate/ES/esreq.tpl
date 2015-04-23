SELECT tqr.id tqrid,qr.id qrid,tqr.*,qr.*,te.`name`,com.short_name,emp.name as emp_name, vt.vip_level
FROM 73go_tmc_qsx_rec tqr
LEFT JOIN 73go_qsx_req qr ON tqr.req_id=qr.id
LEFT JOIN 73go_tmc_employee te ON tqr.rec_emp_id=te.id
LEFT JOIN 73go_tmc tmc ON te.tmc_id=tmc.id  
LEFT JOIN 73go_user u ON qr.u_id=u.id
LEFT JOIN 73go_employee emp ON emp.u_id=qr.u_id
LEFT JOIN 73go_company com ON emp.co_id=com.id
LEFT JOIN 73go_vip_table vt ON vt.tmc_id = tmc.id and vt.emp_id=emp.id
WHERE tqr.`status`=1 AND qr.`status`=0
{if $bygrp}
and (
{* ����$bygrp�����������Ƿ���ȡͬ���ͬ�µĵ��� *}
{if $bygrp eq 1 or $bygrp eq 3}
tqr.rec_emp_id in 
		(select a.emp_id from  73go_tmc_team_member  as a 
		WHERE a.team_id in
		(select ttm.team_id from 73go_tmc_team tt	
			LEFT JOIN 73go_tmc_team_member ttm ON tt.id=ttm.team_id
		 	LEFT JOIN 73go_tmc_employee te ON ttm.emp_id=te.id 
		 	WHERE
	{if $u_id}
		te.u_id= {$u_id}
	{else}
		ttm.emp_id = {$emp_id}
	{/if}
		))
{/if}
{if $bygrp eq 3}
		OR
{/if}
{if $bygrp eq 2 or $bygrp eq 3}
	{if $u_id}
		tqr.rec_emp_id =(select b.id from 73go_tmc_employee b where b.u_id={$u_id})
	{else}
		tqr.rec_emp_id = {$emp_id}
	{/if}
{/if}
	  )
{/if}	   
ORDER BY vip_level desc,tqr.rec_time asc
{if $Page} 
limit {$Page->firstRow} , {$Page->listRows}
{/if}


