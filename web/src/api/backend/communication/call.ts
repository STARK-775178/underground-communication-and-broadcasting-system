import createAxios from '/@/utils/axios'

export function callApi(extension: string) {
    return createAxios({
        url: '/admin/communication.Call/call?server=1&extension='+extension,
        method: 'get',
    })
}

export function hangupApi(extension: string) {
    return createAxios({
        url: '/admin/communication.Call/hangup?server=1&extension='+extension,
        method: 'get',
    })
}

//hangupExtensionsApi post请求，参数为数组
export function hangupExtensionsApi(data: anyObj) {
    return createAxios({
        url: '/admin/communication.Call/hangUpExtensions',
        method: 'post',
        data: data,
    })
}

export function hangupAllApi(data: anyObj) {
    return createAxios({
        url: '/admin/communication.Call/hangupAll',
        method: 'get',
        data: data,
    })
}
