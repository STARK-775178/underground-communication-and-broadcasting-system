import createAxios from '/@/utils/axios'

export function addBroadcastRecord(data: anyObj) {
    return createAxios({
        url: '/admin/broadcast.Record/add',
        method: 'post',
        data: data,
    })
}

