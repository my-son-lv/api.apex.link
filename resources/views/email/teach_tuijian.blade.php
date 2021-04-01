<div style="width: 800px;">
    <!--顶部-->
    <div style="font-size: 24px;
    text-align: center;
    font-weight: 600;
    height: 60px;
    background: #1F2532;
    color: white;
    line-height: 60px;">TOP JOBS PICKS FOR YOU</div>
    <!--顶部-->
    <div style="padding: 0 30px;">
        @foreach($data['list'] as $k => $v)
        <!--职位item-->
        <a style="display: block;cursor: pointer;text-decoration: none;margin-bottom: 22px;" href="https://m.teach.apex.link/#/jobsDetails?id={{$v['id']}}&cid={{$v['cid']}}" target="_blank">
            <div style="width: 100px;height: 100px;display: inline-block;vertical-align: bottom;">
                <img src="{{$v['logo']}}" style="width: 100px;height: 100px;">
            </div>

            <div style="display: inline-block;margin-left: 30px;max-width: 570px;">
                <div style="line-height: 50px;font-size: 24px;width: 570px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color: #4A90E2">{{$v['name']}}</div>
                <div style="color: #000 !important;"><img src="https://files.apex.link/apexlink/2021030410545238092626.png" style="width: 20px;height: 20px;margin-right: 10px;vertical-align: baseline;">{{$v['job_city']}}</div>
                <div ><img src="https://files.apex.link/apexlink/2021030412325214585995.png" style="width: 20px;height: 20px;margin-right: 10px;vertical-align: baseline;"><span style="color: #000 !important;"><span style="color: #FF6010 !important;font-size: 20px;">￥{{$v['pay']}}</span><span style="color: #000 !important;"><span style="color: #000 !important;font-size: unset;">{{$v['pay_unit']}}</span></span></div>
                <div style="color: #000 !important;"><img src="https://files.apex.link/apexlink/2021030412312222277866.png" style="width: 20px;height: 20px;margin-right: 10px;vertical-align: baseline;">{{$v['job_type']}}</div>
            </div>
        </a >
        <!--职位item-->
        @endforeach
        <a style="display: block;cursor: pointer;margin-bottom: 22px;text-align: center;font-style:oblique;color: #4A90E2;" href="https://m.teach.apex.link/#/jobs" target="_blank">See all jobs</a>
    </div>

    <!--底部-->
    <div style="background: #1F2532;color: #fff;padding: 0 30px;overflow: hidden;">
        <div style="line-height: 24px;font-size: 24px;margin-top: 20px;">APEX GLOBAL</div>
        <div style="line-height: 12px;font-size: 12px;margin-top: 16px;">AI-powered ESL teacher recruitment platform</div>

        <div style="display: flex;line-height: 12px;font-size: 12px;margin-top: 30px;">
            <div style="width: 33.3%;    color: #fff !important;">Contact: +86 17001213999</div>
            <div style="width: 33.3%;    color: #fff !important;">Email: service@apex.link</div>
            <div style="width: 33.3%;    color: #fff !important;">Find us on Social Media</div>
        </div>

        <div style="margin-top: 20px;margin-bottom: 20px;">
            <a href="https://www.facebook.com/Apex-Global-100338515321616" target="_blank"><img style="width: 24px;height: 24px;margin-right: 4px;cursor: pointer;" src="https://files.apex.link/apexlink/2021030411211234265705.png"></a>
            <a href="https://www.linkedin.com/company/apexglobal%E5%AF%B0%E7%90%83%E9%98%BF%E5%B8%95%E6%96%AF/?viewAsMember=true" target="_blank"><img style="width: 24px;height: 24px;margin-right: 4px;cursor: pointer;" src="https://files.apex.link/apexlink/2021030411215711112008.png"></a>
            <a href="https://www.instagram.com/apex_global_lee/" target="_blank"><img style="width: 24px;height: 24px;margin-right: 4px;cursor: pointer;" src="https://files.apex.link/apexlink/2021030411285075368341.png"></a>
            <a href="https://twitter.com/apexglobal4/" target="_blank"><img style="width: 24px;height: 24px;margin-right: 4px;cursor: pointer;" src="https://files.apex.link/apexlink/2021030411242981827875.png" ></a>
        </div>
    </div>
    <!--底部-->
</div>