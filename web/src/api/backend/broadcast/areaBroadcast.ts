import createAxios from '/@/utils/axios'

export function areaBroadcast(data: anyObj) {
    return createAxios({
        url: '/admin/broadcast.AreaBroadcast/areaBroadcast?server=1',
        method: 'post',
        data: data,
    })
}


