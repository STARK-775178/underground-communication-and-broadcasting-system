import createAxios from '/@/utils/axios'


export function deviceListApi() {
    return createAxios({
        url: '/admin/device.Device/index?order=id,asc',
        method: 'get',
    })
}


export function areaListApi() {
    return createAxios({
        url: '/admin/device.Area/index?=id,asc',
        method: 'get',
    })
}
