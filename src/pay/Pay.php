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
     * 查询订单
     * @param string $transaction_id 微信返回的支付订单号
     * @return mixed
     */
    public function selectOrder($transaction_id = '');

    /**
     * 关闭订单
     * @param string $out_trade_no 平台订单号
     * @return mixed
     */
    public function closeOrder($out_trade_no = '');

    /**
     * 通知地址
     * @param array $data
     * @return mixed
     */
    public function notify($data = []);

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
}