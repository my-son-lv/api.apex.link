<?php

namespace App\Http\Controllers\Index;

use App\Jobs\SendWxNotice;
use App\Models\Checked;
use App\Models\Code;
use App\Models\Companys;
use App\Models\Country;
use App\Models\Education;
use App\Models\Event;
use App\Models\Files;
use App\Models\ImUser;
use App\Models\JobApplication;
use App\Models\Member;
use App\Http\Controllers\Controller;
use App\Models\MemberInfo;
use App\Models\MemberInfoChecked;
use App\Models\MemberInfoCheckedLog;
use App\Models\Notice;
use App\Models\Official;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MemberController extends Controller
{
    private $mail;

    public function __construct(Mail $mail)
    {
        $this->mail = $mail;
    }

    public function jobApplication(Request $request)
    {
        $jid = $request->get('jid', 0);
        $cid = $request->get('cid', 0);
        if (!$jid || !$cid) {
            return $this->fail(2000001);
        }
        //判断会员是否审核通过
        $memebrInfo = MemberInfo::where('mid', $request->member->id)->first();
        if (!$memebrInfo) {
            return $this->fail(2000214);
        }
        //查找是否一分钟内申请过
        $mid = $request->member->id;
        $app = JobApplication::where(['jid' => $jid, 'mid' => $mid])->orderBy('id', 'desc')->first();
        if ($app && (time() - strtotime($app->created_at)) < 60) {
            return $this->fail(100007);
        }
        JobApplication::create(['jid' => $jid, 'cid' => $cid, 'mid' => $mid]);

        $company = Companys::find($cid);
        if($company->unionid && config('app.env') == 'production') {
            $officials = Official::where('unionid', $company->unionid)->where('status', 1)->first();
            $read_no = JobApplication::where('cid',$company->id)->where('read_flg',1)->count();
            //发送微信公众号通知
            $wxNoticeData = [
                'openid' => $officials->openid,
                'type' => 9,
                'title' => '尊敬的用户，您收到了新的简历，请您留意查阅。',
                'memo' => '您可以点击详情，立即查看简历！',
                'key' => [
                    'keyword1' => '1份',//新简历,
                    'keyword2' => $read_no.'份'//未读,
                ],
            ];
            $this->dispatch(new SendWxNotice($wxNoticeData));
        }
        return $this->success();
    }

    /**
     * 获取外教客户在IM中的账号ID  目前客户ID定为1
     *
     * @param Request $request
     * @return CompanyController
     */
    public function getImAdviser(Request $request)
    {
        $user = User::find(1);
        $im = ImUser::where('type', 3)->where('user_id', $user->id)->first();
        return $this->success(['im_user_id' => $im->id, 'name' => $user->name]);
    }

    /**
     * 上传附件简历
     * @return MemberController
     */
//    public function addNotes()
//    {
//        $token = Input::get('token', 0);
//        $notes = Input::get('id', '');
//        $member = Member::where('token', $token)->first();
//        $data = MemberInfo::where('mid', $member->id)->first();
//        $data1 = MemberInfoChecked::where('mid', $member->id)->first();
//        if (!$notes) {
//            return $this->fail(2000005);
//        }
//        //未提交过资料情况
//        if (!$data && !$data1) {
//            $model = new MemberInfoChecked();
//            $model->notes = $notes;
//            $model->mid = $member->id;
//            if ($model->save()) {
//                return $this->success();
//            } else {
//                return $this->fail();
//            }
//        }
//        //审核通过情况
//        if ($data) {
//            $data->notes = $notes;
//            $data1->notes = $notes;
//            DB::beginTransaction();
//            if ($data->save() && $data1->save()) {
//                DB::commit();
//                return $this->success();
//            } else {
//                DB::rollback();
//                return $this->fail();
//            }
//        } else {
//            //未审核通过
//            $data1->notes = $notes;
//            if ($data1->save()) {
//                return $this->success();
//            } else {
//                return $this->fail();
//            }
//        }
//    }

    /**
     * 删除简历接口
     * @return MemberController
     */
//    public function delNotes()
//    {
//        $token = Input::get('token', 0);
//        $member = Member::where('token', $token)->first();
//        $data = MemberInfo::where('mid', $member->id)->first();
//        $data1 = MemberInfoChecked::where('mid', $member->id)->first();
//        //未提交过资料情况
//        if (!$data && !$data1) {
//            return $this->fail(2000005);
//        }
//        //审核通过情况
//        if ($data) {
//            $data->notes = null;
//            $data1->notes = null;
//            DB::beginTransaction();
//            if ($data->save() && $data1->save()) {
//                DB::commit();
//                return $this->success();
//            } else {
//                DB::rollback();
//                return $this->fail();
//            }
//        } else {
//            //未审核通过
//            $data1->notes = null;
//            if ($data1->save()) {
//                return $this->success();
//            } else {
//                return $this->fail();
//            }
//        }
//    }

    /**
     * 外教详情查看
     * @return \App\Http\Controllers\Admin\MemberController
     */
//    public function view()
//    {
//        $token = Input::get('token', 0);
//        $member = Member::where('token', $token)->first();
//        $data = MemberInfo::with(['education'])->where('mid', $member->id)->first();
//        if (!$data) {
//            return $this->fail(2000005);
//        } else {
//            $country = Country::find($data->nationality);
//            $data['nationality_val'] = $country['code'];
//            $data->videos_path = null;
//            if ($data->videos) {
//                $data->videos_path = Files::whereIn('id', explode(',', $data->videos))->get();
//            }
//            if ($data->photos) {
//                $data->photos_path = Files::whereIn('id', explode(',', $data->photos))->get();
//            }
//            $data->working_city_datas = null;
//            if ($data->working_city) {
//                $city_arr = explode(',', $data->working_city);
//                $citys = [];
//                foreach ($city_arr as $k1 => $v1) {
//                    $tmp_city = Region::find($v1);
//                    $tmp_pro = Region::find($tmp_city->pid);
//                    $citys[] = [
//                        'province_data' => $tmp_pro,
//                        'city_data' => $tmp_city,
//                    ];
//                }
//                $data->working_city_datas = $citys;
//            }
//            $data->china_address_city_data = null;
//            if ($data->china_address && $data->in_domestic == 1) {
//                $tmp_city = Region::find($data->china_address);
//                $tmp_pro = Region::find($tmp_city->pid);
//                $data->china_address_city_data = [
//                    'province_data' => $tmp_pro,
//                    'city_data' => $tmp_city,
//                ];
//            }
//            $data->edu_cert_imgs_path = null;
//            if ($data->edu_cert_imgs) {
//                $data->edu_cert_imgs_path = Files::whereIn('id', explode(',', $data->edu_cert_imgs))->get();
//            }
//            $data->edu_auth_imgs_path = null;
//            if ($data->edu_auth_imgs) {
//                $data->edu_auth_imgs_path = Files::whereIn('id', explode(',', $data->edu_auth_imgs))->get();
//            }
//            $data->notes_path = null;
//            if ($data->notes) {
//                $data->notes_path = Files::whereIn('id', explode(',', $data->notes))->get();
//            }
//            return $this->success($data);
//        }
//    }

    /**
     * 取消审核
     * @return MemberController
     */
//    public function cancelCheck()
//    {
//        $token = Input::get('token', '');
//        $member = Member::where('token', $token)->first();
//        $info = MemberInfoChecked::where('mid', $member->id)->first();
//        if ($info && ($info->status == 1 || $info->status == 3)) {
//            $info->status = 0;
//            if ($info->save()) {
//                return $this->success();
//            } else {
//                return $this->fail();
//            }
//        } else {
//            return $this->fail(2000005);
//        }
//    }

    /**
     * 审核结果是否已读
     * type 0审核驳回已读  1审核成功已读
     * @return MemberController
     */
    public function checkRead()
    {
        $token = Input::get('token', '');
        $type = Input::get('type', 0);//0 审核驳回已读 1审核成功已读
        $member = Member::where('token', $token)->first();
        if ($type == 0) {
            $info = MemberInfoChecked::where('mid', $member->id)->first();
            if ($info) {
                $flg = MemberInfoCheckedLog::where('id', $info->check_log_id)->update(['flg' => 2]);
                if ($flg) {
                    return $this->success();
                } else {
                    return $this->fail();
                }
            } else {
                return $this->fail();
            }
        } elseif ($type == 1) {
            $info = MemberInfoChecked::where('mid', $member->id)->first();
            if ($info) {
                $flg = MemberInfoCheckedLog::where('id', $info->check_log_id)->update(['flg' => 2]);
                if ($flg) {
                    return $this->success();
                } else {
                    return $this->fail();
                }
            } else {
                return $this->fail();
            }
        }
    }

    /**
     * 修改用户状态变为新用户
     * @return MemberController
     */
    /*public function upStatusToNewUser()
    {
        $token = Input::get('token', '');
        $member = Member::where('token', $token)->first();
        $data['status'] = 0;
        $flg = MemberInfoChecked::where('mid', $member->id)->update($data);
        if ($flg) {
            return $this->success();
        } else {
            return $this->fail();
        }
    }*/



    //移动端 保存提交草稿 第一也页
//    public function firstMobileComment(Request $request)
//    {
//        $name = Input::get('name', '');
//        $last_name = Input::get('last_name', '');
//        $sex = Input::get('sex', 2);  //0男 1女 2未知
//        $brithday = Input::get('brithday', null);
//        $nationality = Input::get('nationality', null);
//        $photos = Input::get('photos', null);//照片 多个逗号分隔
//        $abroad_address = Input::get('abroad_address', null);//国外住址
//        $in_domestic = Input::get('in_domestic', 0);//是否在国内 0不在 1在
//        $visa_type = Input::get('visa_type', '');//签证类型  1 Z  2 M 3 F 4 X 5 others
//        $visa_exp_date = Input::get('visa_exp_date', '');
//        $china_address = Input::get('china_address', '');
//        $wechat = $request->get('wechat', '');
//        $phone = $request->get('phone', '');
//        $area_code = $request->get('area_code', '');
//        $country = $request->get('country', '');
//        $comm_type = $request->get('comm_type', 1);
//
//        $token = Input::get('token', '');
//        $member = Member::where('token', $token)->first();
//        $model = MemberInfoChecked::where('mid', $member->id)->first();
//        if (!$model) {
//            $model = new MemberInfoChecked();
//        }
//        $model->comm_type = $comm_type;
//        $model->mid = $member->id;
//        $model->name = $name;
//        $last_name && $model->last_name = $last_name;
//        $model->sex = $sex;
//        $brithday && $model->brithday = $brithday;
//        $model->nationality = $nationality;
//        $abroad_address && $model->abroad_address = $abroad_address;
//        $photos && $model->photos = $photos;
//        $visa_exp_date && $model->visa_exp_date = $visa_exp_date;
//        $wechat && $model->wechat = $wechat;
//        $phone && $model->phone = $phone;
//        $area_code && $model->area_code = $area_code;
//        $model->in_domestic = $in_domestic;
//        if ($in_domestic == 1) {
//            $visa_type && $model->visa_type = $visa_type;
//            $china_address && $model->china_address = $china_address;
//            $model->country = null;
//        } else {
//            $model->visa_type = null;
//            $model->china_address = null;
//            $country && $model->country = $country;
//        }
//        if ($model->save()) {
//            return $this->success();
//        } else {
//            return $this->fail();
//        }
//    }
//
//    /**
//     * 移动端 第二页提交
//     * @param Request $request
//     * @return MemberController
//     */
//    public function twoMobileComment(Request $request)
//    {
//        $token = Input::get('token', '');
//        $school = Input::get('school', '');
//        $major = Input::get('major', '');
//        $university = Input::get('university', null);
//        $edu_cert_flg = Input::get('edu_cert_flg', null);
//        $edu_cert_imgs = Input::get('edu_cert_imgs', null);
//        $edu_auth_flg = Input::get('edu_auth_flg', null);
//        $edu_auth_imgs = Input::get('edu_auth_imgs', null);
//        $education = Input::get('education', null);
//
//        $member = Member::where('token', $token)->first();
//        $model = MemberInfoChecked::where('mid', $member->id)->first();
//        $model->education()->delete();
//        $model->university = $university;
//        $model->school = $school;
//        $model->major = $major;
//        $model->edu_cert_flg = $edu_cert_flg;
//        $model->edu_cert_imgs = $edu_cert_imgs;
//        $model->edu_auth_flg = $edu_auth_flg;
//        $model->edu_auth_imgs = $edu_auth_imgs;
//        if ($model->save()) {
//            $model->education()->createMany(json_decode($education, true));
//            return $this->success();
//        } else {
//            return $this->fail();
//        }
//    }

    /**
     * 移动端第三页提交
     * @param Request $request
     * @return MemberController
     */
//    public function threeMobileComment(Request $request)
//    {
//        $working_seniority = Input::get('working_seniority', null);//工作年限
//        $work_flg = Input::get('work_flg', 0);//在职  0否 1是
//        $pay_type = Input::get('pay_type', 1);
//        $working_city = Input::get('working_city', null);//期望工作地址  0不限
//        $notes = Input::get('notes', null);//简历
//        $desc = Input::get('desc', null);//个人简介
//        $videos = Input::get('videos', null);//视频 多个逗号分隔
//        $type = Input::get('type', 0);//0草稿 1提交审核
//        $school_type = Input::get('school_type', '');
//        $student_age = Input::get('student_age', '');
//        $work_type = Input::get('work_type', '');
//        $job_type = Input::get('job_type', 3);
//        $job_work_type = Input::get('job_work_type', 3);
//
//        $token = Input::get('token', '');
//        $member = Member::where('token', $token)->first();
//        $model = MemberInfoChecked::where('mid', $member->id)->first();
//        $model->working_seniority = $working_seniority;
//        $model->work_flg = $work_flg;
//        $model->pay_type = $pay_type;
//        $model->working_city = $working_city;
//        $model->notes = $notes;
//        $model->desc = $desc;
//        $model->videos = $videos;
//        $model->school_type = $school_type;
//        $model->student_age = $student_age;
//        $model->work_type = $work_type;
//        $model->job_type = $job_type;
//        $model->job_work_type = $job_work_type;
//        if ($type == 1) {
//            $model->status = 1;
//            $model->submit_time = date("Y-m-d H:i:s", time());
//            if (!$model->name || !$model->nationality || !$model->university || !$model->pay_type) {
//                return $this->fail(2000205);
//            }
//        }
//        if ($model->save()) {
//            if ($type == 1) {
//                Event::addEvent($model->name . ' ' . $model->last_name . '提交入驻申请', $member->id);
//            }
//            if ($type == 1 && config('app.env') == 'production') {
//                //获取运营部通知手机好
//                $phones = $this->getYunYingUserPhone();
//                //获取通知内容
//                $Feishu['teach_name'] = $model->name . ' ' . $model->last_name;
//                $Feishu['time'] = date("Y年m月d日 H:i");
//                $this->FeiShuSendText($phones, returnFeiShuMsg(4, $Feishu));
//            }
//            return $this->success();
//        } else {
//            return $this->fail();
//        }
//    }

    /**
     * 移动端获取草稿信息 type第几页
     * @param Request $request
     * @return MemberController
     */
//    public function getCommentInfo(Request $request)
//    {
//        $type = $request->get('type', 1);
//        $token = Input::get('token', '');
//        $member = Member::where('token', $token)->first();
//        $model = MemberInfoChecked::where('mid', $member->id)->first();
//        if (!$model) {
//            return $this->success([]);
//        } else {
//            switch ($type) {
//                case 1:
//                    $model = MemberInfoChecked::where('mid', $member->id)->first([
//                        'name', 'last_name', 'sex', 'brithday', 'nationality', 'abroad_address', 'photos', 'in_domestic', 'visa_type', 'visa_exp_date', 'china_address', 'wechat', 'phone', 'country', 'area_code', "comm_type"
//                    ]);
//                    $country = Country::find($model->nationality);
//                    $model->nationality_val = $country['code'];
//                    $model->country_val = null;
//                    if ($model->country) {
//                        $country = Country::find($model->country);
//                        $model->country_val = $country['code'];
//                    }
//                    if ($model->photos) {
//                        $model->photos_path = Files::whereIn('id', explode(',', $model->photos))->get();
//                    }
//                    $model->working_city_datas = null;
//                    if ($model->working_city) {
//                        $city_arr = explode(',', $model->working_city);
//                        $citys = [];
//                        foreach ($city_arr as $k => $v) {
//                            $tmp_city = Region::find($v);
//                            $tmp_pro = Region::find($tmp_city->pid);
//                            $citys[] = [
//                                'province_data' => $tmp_pro,
//                                'city_data' => $tmp_city,
//                            ];
//                        }
//                        $model->working_city_datas = $citys;
//                    }
//                    $model->china_address_city_data = null;
//                    if ($model->china_address && $model->in_domestic == 1) {
//                        $tmp_city = Region::find($model->china_address);
//                        $tmp_pro = Region::find($tmp_city->pid);
//                        $model->china_address_city_data = [
//                            'province_data' => $tmp_pro,
//                            'city_data' => $tmp_city,
//                        ];
//                    }
//                    return $this->success($model);
//                    break;
//                case 2:
//                    $model = MemberInfoChecked::with(['education'])->where('mid', $member->id)->first([
//                        'school', 'major', 'university', 'edu_cert_flg', 'edu_cert_imgs', 'edu_auth_flg', 'edu_auth_imgs', 'mid'
//                    ]);
//                    $model->edu_cert_imgs_path = null;
//                    if ($model->edu_cert_imgs) {
//                        $model->edu_cert_imgs_path = Files::whereIn('id', explode(',', $model->edu_cert_imgs))->get();
//                    }
//                    $model->edu_auth_imgs_path = null;
//                    if ($model->edu_auth_imgs) {
//                        $model->edu_auth_imgs_path = Files::whereIn('id', explode(',', $model->edu_auth_imgs))->get();
//                    }
//                    return $this->success($model);
//                    break;
//                case 3:
//                    $model = MemberInfoChecked::where('mid', $member->id)->first([
//                        'working_seniority', 'work_flg', 'pay_type', 'working_city', 'notes', 'desc', 'videos', 'school_type', 'work_type', 'student_age', 'job_type', 'job_work_type'
//                    ]);
//                    $model->notes_path = null;
//                    if ($model->notes) {
//                        $model->notes_path = Files::whereIn('id', explode(',', $model->notes))->get();
//                    }
//                    $model->videos_path = null;
//                    if ($model->videos) {
//                        $model->videos_path = Files::whereIn('id', explode(',', $model->videos))->get();
//                    }
//                    $model->working_city_datas = null;
//                    if ($model->working_city) {
//                        $city_arr = explode(',', $model->working_city);
//                        $citys = [];
//                        foreach ($city_arr as $k => $v) {
//                            $tmp_city = Region::find($v);
//                            $tmp_pro = Region::find($tmp_city->pid);
//                            $citys[] = [
//                                'province_data' => $tmp_pro,
//                                'city_data' => $tmp_city,
//                            ];
//                        }
//                        $model->working_city_datas = $citys;
//                    }
//                    return $this->success($model);
//                    break;
//            }
//        }
//
//    }

    /**
     *修改详情数据
     * @return MemberController
     */
    public function updateInfo(Request $request)
    {
        $member = $request->member;//用户
        $memberInfoCheck = MemberInfoChecked::where('mid', $member->id)->first();
        $memberInfo = MemberInfo::where('mid', $member->id)->first();
        DB::beginTransaction();
        try {
            $data = $request->all();
            unset($data['member']);
            //创建教育经历
            if($request->education){
                $memberInfoCheck->education()->delete();
                $memberInfoCheck->education()->createMany(json_decode($request->education, true));
                unset($data['education']);
            }
            //创建工作经历
            if($request->work_experiences){
                $memberInfoCheck->work()->delete();
                $memberInfoCheck->work()->createMany(json_decode($request->work_experiences, true));
                unset($data['work_experiences']);
            }
            //更新信息
            $memberInfoCheck->update($data);
            $memberInfo->update($data);
            $this->dispatch(new \App\Jobs\JobMate(['mid' => $memberInfoCheck->mid , 'type' => 1 ]));
            DB::commit();
            return $this->success();
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('更新用户信息出错了：' . $e->getMessage());
            return $this->fail();
        }
    }


    public function getTeachInfo(Request $request){
        $member = $request->member;
        $model = MemberInfo::where('mid', $member->id)->first();
        $model = $model->with([
            'education',
            'work',
            'nationality_val',
            'videos_path',
            'photos_path',
            'edu_cert_imgs_path',
            'edu_auth_imgs_path',
            'celta_img_path',
            'cert_other_img_path',
            'country_val',
            'university_img_path'
        ])->where('mid', $member->id)->first();
        $model->working_city_datas = null;
        $model->email = $member->email;
        if ($model->working_city) {
            $city_arr = explode(',', $model->working_city);
            $citys = [];
            foreach ($city_arr as $k => $v) {
                $tmp_city = Region::find($v);
                $tmp_pro = Region::find($tmp_city->pid);
                $citys[] = [
                    'province_data' => $tmp_pro,
                    'city_data' => $tmp_city,
                ];
            }
            $model->working_city_datas = $citys;
        }
        $model->china_address_city_data = null;
        if ($model->china_address) {
            $tmp_city = Region::find($model->china_address);
            $tmp_pro = Region::find($tmp_city->pid);
            $model->china_address_city_data = [
                'province_data' => $tmp_pro,
                'city_data' => $tmp_city,
            ];
        }
        $model->notes_path = null;
        if ($model->notes) {
            $model->notes_path = Files::whereIn('id', explode(',', $model->notes))->get();
        }
        return $this->success($model);
    }


    //获取提交草稿
    public function getTeachCommentInfo(Request $request)
    {
        $member = $request->member;
        $model = MemberInfoChecked::where('mid', $member->id)->first();
        $model = $model->with([
            'education',
            'work',
            'nationality_val',
            'videos_path',
            'photos_path',
            'edu_cert_imgs_path',
            'edu_auth_imgs_path',
            'celta_img_path',
            'cert_other_img_path',
            'country_val',
            'university_img_path'
        ])->where('mid', $member->id)->first();
        $model->working_city_datas = null;
        if ($model->working_city) {
            $city_arr = explode(',', $model->working_city);
            $citys = [];
            foreach ($city_arr as $k => $v) {
                $tmp_city = Region::find($v);
                $tmp_pro = Region::find($tmp_city->pid);
                $citys[] = [
                    'province_data' => $tmp_pro,
                    'city_data' => $tmp_city,
                ];
            }
            $model->working_city_datas = $citys;
        }
        $model->china_address_city_data = null;
        if ($model->china_address) {
            $tmp_city = Region::find($model->china_address);
            $tmp_pro = Region::find($tmp_city->pid);
            $model->china_address_city_data = [
                'province_data' => $tmp_pro,
                'city_data' => $tmp_city,
            ];
        }
        $model->notes_path = null;
        if ($model->notes) {
            $model->notes_path = Files::whereIn('id', explode(',', $model->notes))->get();
        }
        return $this->success($model);
    }

    /**
     * 外教入驻提交信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function infoComment(Request $request)
    {
        $type = $request->get('type',0);//0草稿 1提交
        $member = $request->member;//用户
        $memberInfoCheck = MemberInfoChecked::where('mid', $member->id)->first();
        DB::beginTransaction();
        try {
            $data = $request->all();
            unset($data['member']);
            unset($data['education']);
            unset($data['work_experiences']);
            if (!$memberInfoCheck) {
                $memberInfoCheck = new MemberInfoChecked();
                $memberInfoCheck = $memberInfoCheck->create($data);
                if($request->education){
                    $memberInfoCheck->education()->createMany(json_decode($request->education, true));
                }
                if($request->work_experiences){
                    $memberInfoCheck->work()->createMany(json_decode($request->work_experiences, true));
                }
            } else {
                //提交添加提交时间及修改状态
                if($type == 1){
                    $data['status'] = 1;
                    $data['submit_time'] = date("Y-m-d H:i:s", time());
                }
                $memberInfoCheck->update($data);
                //创建教育经历
                if($request->education){
                    $memberInfoCheck->education()->delete();
                    $memberInfoCheck->education()->createMany(json_decode($request->education, true));
                }
                //创建工作经历
                if($request->work_experiences){
                    $memberInfoCheck->work()->delete();
                    $memberInfoCheck->work()->createMany(json_decode($request->work_experiences, true));
                }
            }
            if($type == 1){
                /**
                 * 添加时间日志
                 * 添加平台通知
                 * 发送飞书通知
                 */
                Event::addEvent($memberInfoCheck->name . ' ' . $memberInfoCheck->last_name . '提交入驻申请', $member->id);
                Notice::addNotice(returnNoticeMsg(['teach_name' => $memberInfoCheck->name . ' ' . $memberInfoCheck->last_name], 1003), 1, 1003);

                if (config('app.env') == 'production') {
                    $phones = $this->getYunYingUserPhone();
                    $Feishu['teach_name'] = $memberInfoCheck->name . ' ' . $memberInfoCheck->last_name;
                    $Feishu['time'] = date("Y年m月d日 H:i");
                    $this->FeiShuSendText($phones, returnFeiShuMsg(4, $Feishu));
                }
            }
            $this->dispatch(new \App\Jobs\JobMate(['mid' => $memberInfoCheck->mid , 'type' => 1 ]));
            DB::commit();
            return $this->success($memberInfoCheck);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info('更新用户信息出错了：' . $e->getMessage());
            return $this->fail();
        }
    }

    /**
     * pc第二页提交
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
//    public function infoComment2(Request $request)
//    {
//        $member = $request->member;//用户
//        $memberInfoCheck = MemberInfoChecked::where('mid', $member->id)->first();
//        DB::beginTransaction();
//        try {
//            $data = $request->all();
//            //创建教育经历
//            $memberInfoCheck->education()->delete();
//            $memberInfoCheck->education()->createMany(json_decode($request->education, true));
//            //创建工作经历
//            $memberInfoCheck->work()->delete();
//            $memberInfoCheck->work()->createMany(json_decode($request->work_experiences, true));
//            unset($data['education']);
//            unset($data['member']);
//            unset($data['work_experiences']);
//            //更新信息
//            $memberInfoCheck->update($data);
//            DB::commit();
//            return $this->success();
//        } catch (\Exception $e) {
//            DB::rollback();
//            Log::info('更新用户信息出错了：' . $e->getMessage());
//            return $this->fail();
//        }
//    }

//    public function infoComment3(Request $request)
//    {
//        $member = $request->member;//用户
//        $data = $request->all();
//        unset($request->member);
//        $memberInfoCheck = MemberInfoChecked::where('mid', $member->id)->first();
//        if (in_array($memberInfoCheck['status'], [1, 2])) {
//            return $this->fail();
//        }
//        $data['status'] = 1;
//        $data['submit_time'] = date("Y-m-d H:i:s", time());
//        $memberInfoCheck->update($data);
//        /**
//         * 添加时间日志
//         * 添加平台通知
//         * 发送飞书通知
//         */
//        Event::addEvent($memberInfoCheck->name . ' ' . $memberInfoCheck->last_name . '提交入驻申请', $member->id);
//        Notice::addNotice(returnNoticeMsg(['teach_name' => $memberInfoCheck->name . ' ' . $memberInfoCheck->last_name], 1003), 1, 1003);
//        if (config('app.env') == 'production') {
//            $phones = $this->getYunYingUserPhone();
//            $Feishu['teach_name'] = $memberInfoCheck->name . ' ' . $memberInfoCheck->last_name;
//            $Feishu['time'] = date("Y年m月d日 H:i");
//            $this->FeiShuSendText($phones, returnFeiShuMsg(4, $Feishu));
//        }
//        return $this->success();
//    }








}
