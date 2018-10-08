import os


def get_user_click(rating_file):
    if not os.path.exists(rating_file):
        return {}
    fp = open(rating_file)
    num = 0
    user_click = {}
    for line in fp:
        if num == 0:
            num += 1
            continue
        item = line.strip().split(',')
        if len(item) < 4:
            continue
        [userid, itemid, rating, timestamp] = item
        if float(rating) < 3.0:
            continue
        if userid not in user_click:
            user_click[userid] = []
        user_click[userid].append(itemid)
    fp.close()
    return user_click

def get_item_info(item_file):
    if not os.path.exists(item_file):
        return {}
    num = 0
    fp = open(item_file)
    item_info = {}
    for line in fp:
        if num == 0:
            num += 1
            continue
        item = line.strip().split(',')
        if len(item) < 4:
            continue
        [userid, itemid, rating, timestamp] = item
        if float(rating) < 3.0:
            continue
        if userid not in user_click:
            user_click[userid] = []
        user_click[userid].append(itemid)
    fp.close()
    return user_click

user_click = get_user_click("../data/ratings.csv")
print(user_click['2'])