<?php
$private_key = "-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCFDGD7tDsXzFKOG9ZazDF45G1jil1ZBoTznMqN7WOqa2t7pp6J
75yVKPItIHZyxL2YLBmgCRmDCr9rTtmmqIHbDwPV0/jXsjuMLOJqo5ybToqwm+RH
vIjxUja1PK2trB63mP15GFekzVEgrLMSxrFIiupyU9h1Pw4nHNyKEyFgIQIDAQAB
AoGALBehIGlnWAivp0bUb/zRvGW/VsipDPLbJrzkZ8qvR/AXi7/5NG9DLi+Gqqvi
dUB8MK0UxPOfD82FCHP2L4QSnGK4XSEJK8lzTMhvEBNwxxYXgWxywX3q0yGFROwM
tbS+Jks71CpOtYzS8Zx6ACdhwglrsMPKt86uzGrPk06ZA0ECQQDEone6YscSVrZp
HRO5Xg+zt82BDtbik6BKhr0zkVPNUrbPNQ9PjqiFKDQnoUCOk6HMgWXbzfgjrWED
fStveR8ZAkEArTdxfBX9i8jNkG3KxkK2MUPVxjyo19U4XiPCIVlW5QirflthKp4z
XhzxeiXQ7g9HNXAVU7VAHeGpnV/Q8l3SSQJBAI2T+SbPRkxy+MW4NOJr0lxxA9tf
puLerjPazdGaWr9kRdHtf0emDLpLVzoNhaDitUW9CWz44Sg3BrnvXt3VVMkCQFq+
9ydI5gH7eyY1PsxLWuPIZBBs6w/X1qYLGcMa6Nkoh5+1A9yt8L9XbnLNqP1u56Fp
TOMDy8lb1d5qui6fVHECQAopOe4QnnbVxeL8p5FMgn2OJJ6pugnn+Ax1X1UxOQ/l
2Im6cR432desQAdT0Keb0nD+8B1WCqAZHdv2MgXUQK4=
-----END RSA PRIVATE KEY-----";

$passwd = $_POST['passwd'];
$temp = "";//存放解密后的数据

//生成私钥
$pi_key =  openssl_pkey_get_private($private_key);
//私钥解密
openssl_private_decrypt(base64_decode($passwd),$temp,$pi_key);

//输出解密后的数据
echo $temp;