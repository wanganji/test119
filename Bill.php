<?php
namespace app\bf94136271a65872ea73052d4ab9837b\controller;
use app\bf94136271a65872ea73052d4ab9837b\model\User as UserModel;
use app\bf94136271a65872ea73052d4ab9837b\model\Order as OrderModel;
use app\bf94136271a65872ea73052d4ab9837b\controller\Common;
use think\helper\Time;
class Bill extends Common
{

    //全部用户列表
    public function lst(){
        if(request()->isPost()) {
            $data = input('post.');
            //起始时间
            $where="1=1";
            if($data['control_date']!=null){
                $time1=$data['control_date'];
                $time1=strtotime($time1);
                $todaystart = strtotime(date('Y-m-d'.'00:00:00',$time1));
               $where.=" and time>=$todaystart";
            }
            //结束时间
            if($data['control_date2']!=null){
                $time2=$data['control_date2'];
                $time2=strtotime($time2);
                $todayend = strtotime(date('Y-m-d'.'00:00:00',$time2+3600*24));
                $where.=" and time<=$todayend";
            }
            //最早日期
            $zao=db("bill")->where($where)->order("time dsc")->value("time");
            //最晚日期
            $wan=db("bill")->where($where)->order("time desc")->value("time");


            $confres=db('bill')->order("time desc")->select();
            $zz=0;
            //纯利
            $cl=0;
            foreach ($confres as $k=>$v) {
                $zz += $v['money'];
            }

            foreach ($confres as $k1=>$v1){
                /*用的优惠券类型*/
                $out_trade_no=$v1['out_trade_no'];
                $coupon_id=db("pay_table")->where("order_number='$out_trade_no'")->value("coupon_id");

                $coupon_display = db("coupon_display")
                    ->where('zid',$coupon_id)
                    ->where("state",1)
                    ->select();


                //有优惠券id记录
                if($coupon_display){
                    //现有优惠券类型
                    $coupon_state = array();
                    foreach ($coupon_display as $k => $v)
                    {
                        array_push($coupon_state, $v['coupon_state']);
                    }
                    /*用的优惠券类型 end*/


                    if (strpos($v1['brand_number'], ',') !== false) {
                        //多订单的情况下
                        $newbill = explode(",", $v1['brand_number']);
                        foreach ($newbill as $k2 => $v2) {
                            $category = db("order")->where("id=$v2")->value("category");

                            if ($category == 1) {
                                $sum = order_number($v2);

                                if (in_array(1, $coupon_state)) {
                                    $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=1 ")->value("couponid");
                                    $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                    //优惠后价格
                                    $yhhjg=$yhqvalue-270;

                                    $cl += $yhhjg * $sum;
                                } else {
                                    $yuanjia = db("goods")->where("good_type=1")->value("good_price");
                                    //优惠后价格
                                    $yhhjg=$yuanjia-270;
                                    $cl += $yhhjg * $sum;
                                }

                            }elseif($category == 2) {
                                $sum = order_number($v2);
                                if (in_array(2, $coupon_state)) {
                                    $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=2 ")->value("couponid");
                                    $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                    //优惠后价格
                                    $yhhjg=$yhqvalue-270;

                                    $cl += $yhhjg * $sum;
                                } else {
                                    $yuanjia = db("goods")->where("good_type=2")->value("good_price");
                                    //优惠后价格
                                    $yhhjg=$yuanjia-270;
                                    $cl += $yhhjg * $sum;
                                }
                            }elseif($category == 3) {
                                if (in_array(3, $coupon_state)) {
                                    $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=3 ")->value("couponid");
                                    $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                    //优惠后价格
                                    $yhhjg=$yhqvalue-450;

                                    $cl += $yhhjg;
                                } else {
                                    $yuanjia = db("goods")->where("good_type=3")->value("good_price");
                                    //优惠后价格
                                    $yhhjg=$yuanjia-450;
                                    $cl += $yhhjg;
                                }
                            }elseif($category == 4) {
                                if (in_array(4, $coupon_state)) {
                                    $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=4 ")->value("couponid");
                                    $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                    //优惠后价格
                                    $yhhjg=$yhqvalue;

                                    $cl += $yhhjg;
                                } else {
                                    $yuanjia = db("goods")->where("good_type=4")->value("good_price");
                                    //优惠后价格
                                    $yhhjg=$yuanjia;
                                    $cl += $yhhjg;
                                }
                            }elseif($category == 5){
                                if (in_array(5, $coupon_state)) {
                                    $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=5 ")->value("couponid");
                                    $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                    //优惠后价格
                                    $yhhjg=$yhqvalue-450;

                                    $cl += $yhhjg;
                                } else {
                                    $yuanjia = db("goods")->where("good_type=5")->value("good_price");
                                    //优惠后价格
                                    $yhhjg=$yuanjia-450;
                                    $cl += $yhhjg;
                                }
                            }

                        }

                        $pt = in_array(1, $coupon_state);
                        $md = in_array(6, $coupon_state);


                        if ($pt == true && $md == true) {
                            $cl=$cl-100;
                        }elseif ($pt == false && $md == true) {
                            //echo 2;
                            $cl=$cl-300;
                        }


                    } else {

                        $v2=$v1['brand_number'];

                        $category = db("order")->where("id=$v2")->value("category");
                        if ($category == 1) {
                            $sum = order_number($v2);

                            if (in_array(1, $coupon_state)) {
                                $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=1 ")->value("couponid");
                                $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                //优惠后价格
                                $yhhjg=$yhqvalue-270;

                                $cl += $yhhjg * $sum;
                            } else {
                                $yuanjia = db("goods")->where("good_type=1")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-270;
                                $cl += $yhhjg * $sum;
                            }

                        }elseif($category == 2) {
                            $sum = order_number($v2);
                            if (in_array(2, $coupon_state)) {
                                $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=2 ")->value("couponid");
                                $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                //优惠后价格
                                $yhhjg=$yhqvalue-270;

                                $cl += $yhhjg * $sum;
                            } else {
                                $yuanjia = db("goods")->where("good_type=2")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-270;
                                $cl += $yhhjg * $sum;
                            }
                        }elseif($category == 3) {
                            if (in_array(3, $coupon_state)) {
                                $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=3 ")->value("couponid");
                                $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                //优惠后价格
                                $yhhjg=$yhqvalue-450;

                                $cl += $yhhjg;
                            } else {
                                $yuanjia = db("goods")->where("good_type=3")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-450;
                                $cl += $yhhjg;
                            }
                        }elseif($category == 4) {
                            if (in_array(4, $coupon_state)) {
                                $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=4 ")->value("couponid");
                                $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                //优惠后价格
                                $yhhjg=$yhqvalue;

                                $cl += $yhhjg;
                            } else {
                                $yuanjia = db("goods")->where("good_type=4")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia;
                                $cl += $yhhjg;
                            }
                        }elseif($category == 5){
                            if (in_array(5, $coupon_state)) {
                                $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=5 ")->value("couponid");
                                $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                //优惠后价格
                                $yhhjg=$yhqvalue-450;

                                $cl += $yhhjg;
                            } else {
                                $yuanjia = db("goods")->where("good_type=5")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-450;
                                $cl += $yhhjg;
                            }
                        }

                        $pt = in_array(1, $coupon_state);
                        $md = in_array(6, $coupon_state);


                        if ($pt == true && $md == true) {
                            $cl=$cl-100;
                        }elseif ($pt == false && $md == true) {
                            //echo 2;
                            $cl=$cl-300;
                        }

                    }



                }else{
                    //没有优惠券id记录

                    if (strpos($v1['brand_number'], ',') !== false) {
                        //多订单的情况下
                        $newbill = explode(",", $v1['brand_number']);
                        foreach ($newbill as $k2 => $v2) {
                            $category = db("order")->where("id=$v2")->value("category");

                            if ($category == 1) {
                                $sum = order_number($v2);

                                $yuanjia = db("goods")->where("good_type=1")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-270;
                                $cl += $yhhjg * $sum;


                            }elseif($category == 2) {
                                $sum = order_number($v2);

                                $yuanjia = db("goods")->where("good_type=2")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-270;
                                $cl += $yhhjg * $sum;

                            }elseif($category == 3) {

                                $yuanjia = db("goods")->where("good_type=3")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-450;
                                $cl += $yhhjg;

                            }elseif($category == 4) {

                                $yuanjia = db("goods")->where("good_type=4")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia;
                                $cl += $yhhjg;

                            }elseif($category == 5){

                                $yuanjia = db("goods")->where("good_type=5")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-450;
                                $cl += $yhhjg;

                            }

                        }
                    } else {

                        $v2=$v1['brand_number'];

                        $category = db("order")->where("id=$v2")->value("category");
                        if ($category == 1) {
                            $sum = order_number($v2);


                            $yuanjia = db("goods")->where("good_type=1")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia-270;
                            $cl += $yhhjg * $sum;


                        }elseif($category == 2) {
                            $sum = order_number($v2);

                            $yuanjia = db("goods")->where("good_type=2")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia-270;
                            $cl += $yhhjg * $sum;

                        }elseif($category == 3) {

                            $yuanjia = db("goods")->where("good_type=3")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia-450;
                            $cl += $yhhjg;

                        }elseif($category == 4) {

                            $yuanjia = db("goods")->where("good_type=4")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia;
                            $cl += $yhhjg;

                        }elseif($category == 5){
                            $yuanjia = db("goods")->where("good_type=5")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia-450;
                            $cl += $yhhjg;
                        }
                    }
                }
            }
            $guifei=$zz-$cl;
            $this->assign([
                'zao'  => $zao,
                'wan' => $wan,
                'confres'=>$confres,
                'zz'=>$zz,
                'cl'=>$cl,
                'guifei'=>$guifei

            ]);
            return view();
        }



        //最早日期
        $zao=db("bill")->order("time dsc")->value("time");
        //最晚日期
        $wan=db("bill")->order("time desc")->value("time");

        $confres=db('bill')->order("time desc")->select();
        $zz=0;
        //纯利
        $cl=0;
        foreach ($confres as $k=>$v) {
            $zz += $v['money'];
        }

        foreach ($confres as $k1=>$v1){
            /*用的优惠券类型*/
            $out_trade_no=$v1['out_trade_no'];
            $coupon_id=db("pay_table")->where("order_number='$out_trade_no'")->value("coupon_id");

            $coupon_display = db("coupon_display")
                ->where('zid',$coupon_id)
                ->where("state",1)
                ->select();


                //有优惠券id记录
            if($coupon_display){
                //现有优惠券类型
                $coupon_state = array();
                foreach ($coupon_display as $k => $v)
                {
                array_push($coupon_state, $v['coupon_state']);
                }
                    /*用的优惠券类型 end*/


                if (strpos($v1['brand_number'], ',') !== false) {
                    //多订单的情况下
                    $newbill = explode(",", $v1['brand_number']);
                    foreach ($newbill as $k2 => $v2) {
                        $category = db("order")->where("id=$v2")->value("category");

                        if ($category == 1) {
                            $sum = order_number($v2);

                            if (in_array(1, $coupon_state)) {
                                $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=1 ")->value("couponid");
                                $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                //优惠后价格
                                $yhhjg=$yhqvalue-270;

                                $cl += $yhhjg * $sum;
                            } else {
                                $yuanjia = db("goods")->where("good_type=1")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-270;
                                $cl += $yhhjg * $sum;
                            }

                        }elseif($category == 2) {
                            $sum = order_number($v2);
                            if (in_array(2, $coupon_state)) {
                                $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=2 ")->value("couponid");
                                $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                //优惠后价格
                                $yhhjg=$yhqvalue-270;

                                $cl += $yhhjg * $sum;
                            } else {
                                $yuanjia = db("goods")->where("good_type=2")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-270;
                                $cl += $yhhjg * $sum;
                            }
                        }elseif($category == 3) {
                            if (in_array(3, $coupon_state)) {
                                $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=3 ")->value("couponid");
                                $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                //优惠后价格
                                $yhhjg=$yhqvalue-450;

                                $cl += $yhhjg;
                            } else {
                                $yuanjia = db("goods")->where("good_type=3")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-450;
                                $cl += $yhhjg;
                            }
                        }elseif($category == 4) {
                            if (in_array(4, $coupon_state)) {
                                $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=4 ")->value("couponid");
                                $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                //优惠后价格
                                $yhhjg=$yhqvalue;

                                $cl += $yhhjg;
                            } else {
                                $yuanjia = db("goods")->where("good_type=4")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia;
                                $cl += $yhhjg;
                            }
                        }elseif($category == 5){
                            if (in_array(5, $coupon_state)) {
                                $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=5 ")->value("couponid");
                                $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                                //优惠后价格
                                $yhhjg=$yhqvalue-450;

                                $cl += $yhhjg;
                            } else {
                                $yuanjia = db("goods")->where("good_type=5")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-450;
                                $cl += $yhhjg;
                            }
                        }

                    }

                    $pt = in_array(1, $coupon_state);
                    $md = in_array(6, $coupon_state);


                    if ($pt == true && $md == true) {
                        $cl=$cl-100;
                    }elseif ($pt == false && $md == true) {
                        //echo 2;
                        $cl=$cl-300;
                    }



                } else {

                    $v2=$v1['brand_number'];

                    $category = db("order")->where("id=$v2")->value("category");
                    if ($category == 1) {
                        $sum = order_number($v2);

                        if (in_array(1, $coupon_state)) {
                            $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=1 ")->value("couponid");
                            $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                            //优惠后价格
                            $yhhjg=$yhqvalue-270;

                            $cl += $yhhjg * $sum;
                        } else {
                            $yuanjia = db("goods")->where("good_type=1")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia-270;
                            $cl += $yhhjg * $sum;
                        }

                    }elseif($category == 2) {
                        $sum = order_number($v2);
                        if (in_array(2, $coupon_state)) {
                            $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=2 ")->value("couponid");
                            $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                            //优惠后价格
                            $yhhjg=$yhqvalue-270;

                            $cl += $yhhjg * $sum;
                        } else {
                            $yuanjia = db("goods")->where("good_type=2")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia-270;
                            $cl += $yhhjg * $sum;
                        }
                    }elseif($category == 3) {
                        if (in_array(3, $coupon_state)) {
                            $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=3 ")->value("couponid");
                            $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                            //优惠后价格
                            $yhhjg=$yhqvalue-450;

                            $cl += $yhhjg;
                        } else {
                            $yuanjia = db("goods")->where("good_type=3")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia-450;
                            $cl += $yhhjg;
                        }
                    }elseif($category == 4) {
                        if (in_array(4, $coupon_state)) {
                            $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=4 ")->value("couponid");
                            $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                            //优惠后价格
                            $yhhjg=$yhqvalue;

                            $cl += $yhhjg;
                        } else {
                            $yuanjia = db("goods")->where("good_type=4")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia;
                            $cl += $yhhjg;
                        }
                    }elseif($category == 5){
                        if (in_array(5, $coupon_state)) {
                            $yhqid = db("coupon_display")->where("zid=$coupon_id and coupon_state=5 ")->value("couponid");
                            $yhqvalue = db("user_coupon")->where("id=$yhqid")->value("voucher_value");
                            //优惠后价格
                            $yhhjg=$yhqvalue-450;

                            $cl += $yhhjg;
                        } else {
                            $yuanjia = db("goods")->where("good_type=5")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia-450;
                            $cl += $yhhjg;
                        }
                    }

                    $pt = in_array(1, $coupon_state);
                    $md = in_array(6, $coupon_state);


                    if ($pt == true && $md == true) {
                        $cl=$cl-100;
                    }elseif ($pt == false && $md == true) {
                        //echo 2;
                        $cl=$cl-300;
                    }

                }



            }else{
                //没有优惠券id记录

                if (strpos($v1['brand_number'], ',') !== false) {
                    //多订单的情况下
                    $newbill = explode(",", $v1['brand_number']);
                    foreach ($newbill as $k2 => $v2) {
                        $category = db("order")->where("id=$v2")->value("category");

                        if ($category == 1) {
                            $sum = order_number($v2);

                                $yuanjia = db("goods")->where("good_type=1")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-270;
                                $cl += $yhhjg * $sum;


                        }elseif($category == 2) {
                            $sum = order_number($v2);

                                $yuanjia = db("goods")->where("good_type=2")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-270;
                                $cl += $yhhjg * $sum;

                        }elseif($category == 3) {

                                $yuanjia = db("goods")->where("good_type=3")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-450;
                                $cl += $yhhjg;

                        }elseif($category == 4) {

                                $yuanjia = db("goods")->where("good_type=4")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia;
                                $cl += $yhhjg;

                        }elseif($category == 5){

                                $yuanjia = db("goods")->where("good_type=5")->value("good_price");
                                //优惠后价格
                                $yhhjg=$yuanjia-450;
                                $cl += $yhhjg;

                        }

                    }
                } else {

                    $v2=$v1['brand_number'];

                    $category = db("order")->where("id=$v2")->value("category");
                    if ($category == 1) {
                        $sum = order_number($v2);


                            $yuanjia = db("goods")->where("good_type=1")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia-270;
                            $cl += $yhhjg * $sum;


                    }elseif($category == 2) {
                        $sum = order_number($v2);

                            $yuanjia = db("goods")->where("good_type=2")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia-270;
                            $cl += $yhhjg * $sum;

                    }elseif($category == 3) {

                            $yuanjia = db("goods")->where("good_type=3")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia-450;
                            $cl += $yhhjg;

                    }elseif($category == 4) {

                            $yuanjia = db("goods")->where("good_type=4")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia;
                            $cl += $yhhjg;

                    }elseif($category == 5){
                            $yuanjia = db("goods")->where("good_type=5")->value("good_price");
                            //优惠后价格
                            $yhhjg=$yuanjia-450;
                            $cl += $yhhjg;
                    }
                }
            }
        }



        $guifei=$zz-$cl;

        $this->assign('confres',$confres);

        $this->assign([
            'zao'  => $zao,
            'wan' => $wan,
            'confres'=>$confres,
            'zz'=>$zz,
            'cl'=>$cl,
            'guifei'=>$guifei,

        ]);



        return view();
    }










    public function reconciliation(){
        if(request()->isPost()) {
            $data = input('post.');
            //起始时间
            $where="1=1";
            if($data['control_date']!=null){



                $time1=$data['control_date'];
                $time1=strtotime($time1);

                $todaystart = strtotime(date('Y-m-d'.'00:00:00',$time1));


                $where.=" and time>=$todaystart";
            }
            //结束时间
            if($data['control_date2']!=null){
                $time2=$data['control_date2'];
                $time2=strtotime($time2);


                $todayend = strtotime(date('Y-m-d'.'00:00:00',$time2+3600*24));

                $where.=" and time<=$todayend";
            }
            //最早日期
            $zao=db("account")->where($where)->order("time dsc")->value("time");
            //最晚日期
            $wan=db("account")->where($where)->order("time desc")->value("time");

            $confres=db('account')->where($where)->order("time desc")->select();
            $guifei=0;


            foreach ($confres as $k=>$v) {
                $guifei+=$v['fees'];
            }


            $this->assign([
                'zao'  => $zao,
                'wan' => $wan,
                'confres'=>$confres,
                'guifei'=>$guifei,
            ]);
            return view();
        }


        $guifei=0;
        $confres=db("account")->select();
        //最早日期
        $zao=db("account")->order("time dsc")->value("time");
        //最晚日期
        $wan=db("account")->order("time desc")->value("time");

        foreach ($confres as $k=>$v){
            $guifei+=$v['fees'];
        }
        $this->assign('confres',$confres);

        $this->assign([
            'zao'  => $zao,
            'wan' => $wan,
            'confres'=>$confres,
            'guifei'=>$guifei,
        ]);



        return view();
    }





    

    




   

	












}
