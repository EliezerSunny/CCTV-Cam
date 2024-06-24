#!/usr/bin/env python3
# -*- coding: utf-8 -*-
# github.com/EliezerSunny/CCTV-Cam

import requests
import re
import colorama
import random
from requests.structures import CaseInsensitiveDict

colorama.init()

url = "http://www.insecam.org/en/jsoncountries/"

headers = CaseInsensitiveDict()
headers["Accept"] = "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7"
headers["Cache-Control"] = "max-age=0"
headers["Connection"] = "keep-alive"
headers["Host"] = "www.insecam.org"
headers["Upgrade-Insecure-Requests"] = "1"
headers["User-Agent"] = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36"

try:
    resp = requests.get(url, headers=headers)
    resp.raise_for_status()
    data = resp.json()
    countries = data['countries']

    print("""
    \033[1;31m\033[1;37m ██████╗ █████╗ ███╗   ███╗      ██╗  ██╗ █████╗  ██████╗██╗  ██╗███████╗██████╗ ███████╗
    ██╔════╝██╔══██╗████╗ ████║      ██║  ██║██╔══██╗██╔════╝██║ ██╔╝██╔════╝██╔══██╗██╔════╝
    ██║     ███████║██╔████╔██║█████╗███████║███████║██║     █████╔╝ █████╗  ██████╔╝███████╗
    ██║     ██╔══██║██║╚██╔╝██║╚════╝██╔══██║██╔══██║██║     ██╔═██╗ ██╔══╝  ██╔══██╗╚════██║
    ╚██████╗██║  ██║██║ ╚═╝ ██║      ██║  ██║██║  ██║╚██████╗██║  ██╗███████╗██║  ██║███████║
    \033[1;31m ╚═════╝╚═╝  ╚═╝╚═╝     ╚═╝      ╚═╝  ╚═╝╚═╝  ╚═╝ ╚═════╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝╚══════╝
    \033[1;31m                                                                        EliezerSunny \033[1;31m\033[1;37m""")

    for key, value in countries.items():
        print(f'Code : ({key}) - {value["country"]} / ({value["count"]})  ')
        print("")

    country = input("Code(##) : ")

    res = requests.get(f"http://www.insecam.org/en/bycountry/{country}", headers=headers)
    res.raise_for_status()
    last_page = re.findall(r'pagenavigator\("\?page=", (\d+)', res.text)[0]

    with open(f'{country}.txt', 'w') as f:
        for page in range(int(last_page)):
            res = requests.get(
                f"http://www.insecam.org/en/bycountry/{country}/?page={page}",
                headers=headers
            )
            res.raise_for_status()
            find_ip = re.findall(r"http://\d+\.\d+\.\d+\.\d+:\d+", res.text)

            for ip in find_ip:
                print("")
                print("\033[1;31m", ip)
                f.write(f'{ip}\n')
except requests.exceptions.RequestException as e:
    print(f"Request failed: {e}")
except Exception as e:
    print(f"An error occurred: {e}")
finally:
    print("\033[1;37m")
    print(f'\033[37mSave File : {country}.txt')

    exit()
