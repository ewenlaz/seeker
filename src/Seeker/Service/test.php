<?php
//返回协议 一定包涵 ToNode, ToProcess;()
//Node...
if (node = self) {
    //发给自己。。
    if (has local protcool || has service protocol) {
        if (process) {
            if (service) {
                send
            } else {
                unknow service.
            }
        } elseif (broadcast) {
            broadcast to has protocol service
            if (local) {
                local ....
            }
        } else {
            if (local) {
                local ...
            } else {
                pick one service..
            }
        }
    } else {
        unknow protocl;
    }



    if (process) {
        if (service has && has protocol) {
            send
        } elseif (service) {
            unknow protocol.
        } else {
            unknow service.
        }
    } else {
        //broadcast
        if (broadcast) {
            foreach (service ) {
                if (has protocol) {
                    send
                }
            }
        } else {
            if (pick when service has protocol)
        }
    }

    if (local) {

    } elseif (service) {

    } else {
        unknow
    }
}

