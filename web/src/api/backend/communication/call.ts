import createAxios from '/@/utils/axios'

export function callApi() {
    return createAxios({
        url: '/admin/communication.Call/call?server=1',
        method: 'get',
    })
}
