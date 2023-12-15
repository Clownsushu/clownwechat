<?php
namespace clown\wechat\pay;

/**
 * 微信支付接口
 */
interface Pay
{
    /**
     * 统一下单
     * @param array $data
     * @return mixed
     */
    public function getPrePay(array $data);

    /**
     * 通知地址
     * @param array $data
     * @return mixed
     */
    public function notify($data = []);

    /**
     * 订单查询
     * @param $order_code string 订单号, 支持微信订单号和平台订单号
     * @param $order_field string 订单号字段名, transaction_id 代表微信订单号, out_trade_no 代表平台订单号
     * @return mixed
     */
    public function orderQuery($order_code = '', $order_field = 'transaction_id');

    /**
     * 关闭订单
     * @param string $out_trade_no 平台订单号
     * @return mixed
     */
    public function closeOrder($out_trade_no = '');

    /**
     * 退款
     * @param array $data 退款数据
     * @return mixed
     */
    public function refunds(array $data);

    /**
     * 查询单笔退款
     * @param string $out_refund_no 平台退款订单号
     * @return mixed
     */
    public function refundQuery($out_refund_no = '');

    /**
     * 退款通知
     * @param $data array 退款通知数据
     * @return mixed
     */
    public function refundNotify($data = []);
}