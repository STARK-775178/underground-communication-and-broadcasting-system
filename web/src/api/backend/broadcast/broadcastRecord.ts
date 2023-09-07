import createAxios from '/@/utils/axios'

export function addBroadcastRecord(data: anyObj) {
    return createAxios({
        url: '/admin/broadcast.BroadcastRecord/add',
        method: 'post',
        data: data,
    })
}

