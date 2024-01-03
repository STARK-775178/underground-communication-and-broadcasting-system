import createAxios from '/@/utils/axios'
export function insertChannelApi(data: anyObj) {
    return createAxios({
        url: '/admin/communication.Channels/insertChannel',
        method: 'post',
        data: data,
    })
}
