import {$image} from "~/api";

export const $favicon = (path, t = false) => {
    const link = document.querySelector("link[rel*='icon']") || document.createElement('link')
    link.type = 'image/x-icon'
    link.rel = 'shortcut icon'
    let href = $image(path)
    link.href = t ? [href, 't=' + String(new Date() / 1)].join(href.indexOf('?') === -1 ? '?' : '&') : href
    document.getElementsByTagName('head')[0].appendChild(link)
}
