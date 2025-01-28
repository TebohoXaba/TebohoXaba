print("Welcome to your 'Get To Know Me Template'")

print("")

Consent = bool(input("""To get started, we'll be taking some basic iformation from you.
      
Do you consent to us collecting your personal information?: """))

print("")

Yes = True

No = False

True = "Married"

if Consent == True:
    
    Name = input("Please enter your name: ")
    
    print("")
    
    Surname = input("Please enter your family name: ")
    
    print("")
    
    Age = int(input("Please tell us you age: "))
    
    print("")
    
    Address = input("Please enter your suburb or town: ")
    
    print("")
    
    City = input("Whats your city name: ")
    
    print("")
    
    Country = input("Whats your country of origin: ")
    
    print("")
    
    Res_Country = input("Please enter your current resident country (if not the same as country of orrigin) : ")
    
    print("")
    
    Zip_Code = input("What is your zip/postal code: ")
    
    print("")
    
    Memory = input("What is your one most chersihed childhood memory: ")
    
    print("")
    
    First_Car = input("What was the make and model of your first car: ")
    
    print("")
    
    Pet = bool(input("Do you like animals? : "))

    print("")

    if Pet == True:
        Pet = input("What is your favourate animal and breed: ")
    
    else :
        Pet = "I dont like animals"
        
    print("")

    Marital_Status = bool(input("Are you married: "))

    print("")

    if Marital_Status == True:

        Marital_Status = "Married"

        Years_Married = int(input("How long have you been Married: "))

        print("")

        Partner = input("Who is your partners name: ")

        print("")

        Partners_Age = int(input("How old is your partner: "))
    
    else :
        Marital_Status = "Single" 
    
    print("")

    Gender = input("What gender do you identify as: ")

    print("")

    Qualification = input("What is your higest form of education: ")

    print("")

    Education_Institute = input("Where did you obtain your qualification: ")

    print("")

    Year_Obtained = input("When did you graduate: ")

    print("")

    About_Me = (f"""Hi There! 
                
    My name is {Name} {Surname}, and i am {Age}. I live was born in {Country}, and i currently live in {Address}, {Zip_Code}, {City}, in {Res_Country}.
    I am a {Gender}, and i'm happily {Marital_Status} to {Partner} for {Years_Married}. My Partner {Partner} is {Partners_Age}. 
    I currently hold my {Qualification} which i obtained from {Education_Institute}, in {Year_Obtained}. 
    My most cherished Childhood memory is {Memory},
    And my first car was a {First_Car} and my favourite animal is a {Pet}.""")

    print(About_Me)

else :
    print("""Thanks for letting us know!
          
    Unfortunately we cant proceed to collect your information for your 'Get to know me'
          
    Feel free to come back if you change your mind
          
                        Bye for now!""")

exit