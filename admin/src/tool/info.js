import {useStore} from "~/store";
import {$api, $response} from "~/api";

export const getInfo = async (then = () => {
}) => {
  const response = await $api('AdminInfo')
  $response(response, () => {
    const $store = useStore()
    $store.info = response.data.info
    then($store.info)
  })
}
