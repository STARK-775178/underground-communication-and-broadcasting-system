import createAxios from '/@/utils/axios'


export function updateTaskStatusToRunning(headId: number) {
    return createAxios({
        url: '/admin/broadcast.Tasks/updateTaskStatusToRunning?head_id='+headId,
        method: 'get',
    })
}


export function updateTaskStatusToFinish(headId: number) {
    return createAxios({
        url: '/admin/broadcast.Tasks/updateTaskStatusToFinished?head_id='+headId,
        method: 'get',
    })
}
