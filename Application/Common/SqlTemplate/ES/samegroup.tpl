SELECT a.emp_id, emp.u_id 
FROM  73go_tmc_team_member as a
	LEFT OUTER JOIN 73go_tmc_employee as emp ON emp.id = a.emp_id   
WHERE a.team_id in
	 (select ttm.team_id from 73go_tmc_team tt
		LEFT JOIN 73go_tmc_team_member ttm ON tt.id=ttm.team_id
		LEFT JOIN 73go_tmc_employee te ON ttm.emp_id=te.id
		WHERE 1=1
{if $emp_id}
			AND te.id = {$emp_id} 
{/if}		
{if $u_id}
			AND te.u_id = {$u_id}
{/if}
	 )