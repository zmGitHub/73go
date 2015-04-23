{*需要获得数量，就设置 $cond['__GET_COUNT'] = '1'*}
{if $__GET_COUNT}
SELECT COUNT(rec.id) cnt
{else}
SELECT rec.id rec_id, req.*, rec.distr_time, rec.ot_time, rec.rec_time
{/if}
FROM 73go_tmc_qsx_rec rec, 73go_qsx_req req
WHERE rec.req_id = req.id AND rec.status = '0' AND  req.status = '0' AND
    ((`tmc_id` = {$tmcId} ) OR
     (`team_id` in (SELECT team_id FROM 73go_tmc_team_member WHERE emp_id = {$empId})) OR
     (emp_id = {$empId}) OR
     (rec_emp_id = {$empId}))
ORDER BY rec.id desc
{*翻页支持*}
{if $Page}
    limit {$Page->firstRow} , {$Page->listRows}
{/if}