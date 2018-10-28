'''

int(x [,base])将x转换为一个整数
float(x)将x转换到一个浮点数
complex(real [,imag])创建一个复数
str(x)将对象 x 转换为字符串
repr(x)将对象 x 转换为表达式字符串
eval(str)用来计算在字符串中的有效Python表达式,并返回一个对象
tuple(s)将序列 s 转换为一个元组
list(s)将序列 s 转换为一个列表
set(s)转换为可变集合
dict(d)创建一个字典。d 必须是一个序列 (key,value)元组。
frozenset(s)转换为不可变集合
chr(x)将一个整数转换为一个字符
ord(x)将一个字符转换为它的整数值
hex(x)将一个整数转换为一个十六进制字符串
oct(x)将一个整数转换为一个八进制字符串

'''

# 判断表达式

age = 20

if age > 20:
    print('age 大于 20')
elif age > 10:
    print('age小于20大于10')
else:
    print('age小于10')

# 字符串
str = 'beautiful'

print(str[0])
print(str[1:])
print(str[1:-1])
print(str * 2)
print(str + ' ni hao')

# 列表
list = ['abcd', 786, 2.23, 'runoob', 70.2]
tinylist = [123, 'runoob']
print(list)  # 输出完整列表
print(list[0])  # 输出列表第一个元素
print(list[1:3])  # 从第二个开始输出到第三个元素
print(list[2:])  # 输出从第三个元素开始的所有元素
print(tinylist * 2)  # 输出两次列表
print(list + tinylist)  # 连接列表
# 修改列表元素
list[0] = 'ding'
list[0:2] = ['name', 'age']

# 元组
tuple = ('abcd', 786, 2.23, 'runoob', 70.2)
tinytuple = (123, 'runoob')
print(tuple)  # 输出完整元组
print(tuple[0])  # 输出元组的第一个元素
print(tuple[1:3])  # 输出从第二个元素开始到第三个元素
print(tuple[2:])  # 输出从第三个元素开始的所有元素
print(tinytuple * 2)  # 输出两次元组
print(tuple + tinytuple)  # 连接元组

tup1 = ()  # 空元组
tup2 = (20,)  # 一个元素，需要在元素后添加逗号

# Set（集合）
student = {'Tom', 'Jim', 'Mary', 'Tom', 'Jack', 'Rose'}

print(student)  # 输出集合，重复的元素被自动去掉

# 成员测试
if 'Rose' in student:
    print('Rose 在集合中')
else:
    print('Rose 不在集合中')

# set可以进行集合运算
a = set('abracadabra')
b = set('alacazam')

print(a)
print(a - b)  # a和b的差集
print(a | b)  # a和b的并集
print(a & b)  # a和b的交集
print(a ^ b)  # a和b中不同时存在的元素

# Dictionary（字典）
dict = {}
dict['one'] = "1 - 菜鸟教程"
dict[2] = "2 - 菜鸟工具"
tinydict = {'name': 'runoob', 'code': 1, 'site': 'www.runoob.com'}
print(dict['one'])  # 输出键为 'one' 的值
print(dict[2])  # 输出键为 2 的值
print(tinydict)  # 输出完整的字典
print(tinydict.keys())  # 输出所有键
print(tinydict.values())  # 输出所有值

num = 4.5

# 逻辑运算符
a = 10
b = 20
if (a and b):
    print("1 - 变量 a 和 b 都为 true")
else:
    print("1 - 变量 a 和 b 有一个不为 true")

if (a or b):
    print("2 - 变量 a 和 b 都为 true，或其中一个变量为 true")
else:
    print("2 - 变量 a 和 b 都不为 true")

if not (a and b):
    print("5 - 变量 a 和 b 都为 false，或其中一个变量为 false")
else:
    print("5 - 变量 a 和 b 都为 true")

# 成员运算符
a = 10
b = 20
list = [1, 2, 3, 4, 5]
if (a in list):
    print("1 - 变量 a 在给定的列表中 list 中")
else:
    print("1 - 变量 a 不在给定的列表中 list 中")

if (b not in list):
    print("2 - 变量 b 不在给定的列表中 list 中")
else:
    print("2 - 变量 b 在给定的列表中 list 中")

if __name__ == '__main__':
    print('hello world')
